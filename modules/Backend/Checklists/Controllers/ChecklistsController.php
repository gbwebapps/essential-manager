<?php declare(strict_types = 1);

namespace Modules\Backend\Checklists\Controllers;

use App\Controllers\Backend\BackendController;
use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Checklists\Config\Views;
use Modules\Backend\Checklists\Libraries\ChecklistsClass;
use Modules\Backend\Checklists\Models\ChecklistsModel;
use Psr\Log\LoggerInterface;

/**
 * CRUD dei modelli; applicare una checklist restituisce copie dei passi senza salvare task.
 */
class ChecklistsController extends BackendController
{
    protected ChecklistsModel $checklistsModel;
    protected ChecklistsClass $checklistsClass;

    /**
     * @param RequestInterface $request Richiesta.
     * @param ResponseInterface $response Risposta.
     * @param LoggerInterface $logger Logger.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->data['controller'] = 'checklists'; $this->data['entity'] = 'checklists'; $this->data['checklist'] = null;
        $this->checklistsModel = model(ChecklistsModel::class); $this->checklistsClass = new ChecklistsClass();
    }

    /**
     * Usa la stessa lista reale per l'ingresso della sezione.
     * @return string|ResponseInterface Pagina oppure risposta alla richiesta della lista.
     */
    public function index(): string|ResponseInterface
    {
        return $this->showAll();
    }

    /**
     * Valida i filtri e delega paginazione e query al BackendModel tramite ChecklistsModel.
     * @return string|ResponseInterface Lista HTML oppure JSON con record e conteggi aggiornati.
     */
    public function showAll(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $validated = $this->checklistsModel->validateList($this->request->getPost());
            if ( ! $validated['result']): return $this->jsonResponse($this->withValidationMessage($validated)); endif;
            $this->data['posts'] = $validated['data'];
            $this->data['data'] = $this->checklistsModel->getData($validated['data']);
            if ( ! $this->data['data']['result']): return $this->jsonResponse($this->data['data']); endif;
            $this->prepareView('showAll');
            return $this->jsonResponse([
                'result' => true,
                'output' => view(Views::SHOW_ALL_PARTIAL, $this->data),
                'trashCounts' => $this->checklistsModel->getTrashCounts(),
                'navigationCounts' => $this->data['navigationCounts'],
            ]);
        endif;
        $this->prepareView('showAll');
        return $this->render(Views::SHOW_ALL_VIEW, $this->data);
    }

    /**
     * Carica, ricarica o aggiorna il checklist corrente, senza accettare modifiche a campi di sistema.
     * @param string|null $uuid UUID nella rotta GET; nelle POST proviene dal form e viene validato.
     * @return string|ResponseInterface HTML di modifica oppure esito JSON.
     */
    public function edit(?string $uuid = null): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            if ( ! $this->allows('checklists_edit')): return $this->forbidden(); endif;
            if ( ! $this->validateData($posts, $this->checklistsModel->delValidationRules())): return $this->validationResponse(); endif;
            $checklist = $this->checklistOr404($posts['uuid']);
            if ($checklist->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/checklists.messages.cannotModifyDeleted')]); endif;
            if (($posts['action'] ?? null) === 'refresh'):
                $this->data['checklist'] = $checklist;
                $this->prepareView('edit');
                return $this->jsonResponse(['result' => true, 'output' => view(Views::EDIT_PARTIAL, $this->data)]);
            endif;
            $result = $this->checklistsModel->edit($posts);
            if ($result['result']): $this->audit('EDIT_CHECKLISTS', $posts['uuid'], $checklist->name); endif;

            if ($result['result']): $result['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin); endif;
            return $this->jsonResponse($this->withValidationMessage($result));
        endif;
        $this->data['checklist'] = $this->checklistOr404($uuid);
        if ($this->data['checklist']->deleted_at !== null): throw PageNotFoundException::forPageNotFound(); endif;
        $this->prepareView('edit');
        return $this->render(Views::EDIT_VIEW, $this->data);
    }

    /**
     * Mostra il dettaglio di un checklist attivo e i dati collegati in sola lettura.
     * @param string $uuid Identificativo nella rotta.
     * @return string HTML del dettaglio.
     */
    public function show(string $uuid): string
    {
        $this->data['checklist'] = $this->checklistOr404($uuid);
        if ($this->data['checklist']->deleted_at !== null): throw PageNotFoundException::forPageNotFound(); endif;
        $this->prepareView('show');
        return $this->render(Views::SHOW_VIEW, $this->data);
    }

    /**
     * Esegue la cancellazione definitiva autorizzata dal filtro della rotta.
     * @return ResponseInterface Esito JSON.
     */
    public function hardDelete(): ResponseInterface
    {
        return $this->changeDeletion('hardDelete');
    }

    /**
     * Esegue lo spostamento nel cestino del checklist.
     * @return ResponseInterface Esito JSON.
     */
    public function softDelete(): ResponseInterface
    {
        return $this->changeDeletion('softDelete');
    }

    /**
     * Ripristina un checklist cestinato.
     * @return ResponseInterface Esito JSON.
     */
    public function restoreDelete(): ResponseInterface
    {
        return $this->changeDeletion('restoreDelete');
    }

    /**
     * Rilegge le date del checklist dal database senza utilizzare metadati inviati dal browser.
     * @return ResponseInterface HTML dei metadati in JSON.
     */
    public function getMetaData(): ResponseInterface
    {
        if ( ! $this->allows('checklists_show') && ! $this->allows('checklists_edit')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->checklistsModel->delValidationRules())): return $this->validationResponse('action'); endif;
        $this->data['checklist'] = $this->checklistOr404($posts['uuid']);
        return $this->jsonResponse(['result' => true, 'output' => view(Views::META_DATA_PARTIAL, $this->data)]);
    }

    /**
     * Esegue una transizione del cestino validata e ne registra l'audit dopo il commit.
     * @param string $method Metodo interno del model.
     * @return ResponseInterface Risposta JSON.
     */
    private function changeDeletion(string $method): ResponseInterface
    {
        if ( ! $this->allows($method === 'restoreDelete' ? 'checklists_restore' : 'checklists_delete')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->checklistsModel->delValidationRules())): return $this->validationResponse('action'); endif;
        $result = $this->checklistsModel->{$method}($posts);
        if ($result['result']): $this->audit(strtoupper($method) . '_CHECKLISTS', $result['uuid'], $result['name']); endif;
        return $this->jsonResponse($result);
    }

    /**
     * Recupera un checklist con il metodo globale getByUUID oppure restituisce 404.
     * @param string|null $uuid UUID ricevuto dalla rotta o già validato dalla POST.
     * @return object Progetto persistito con i conteggi.
     * @throws PageNotFoundException Se l'identificativo o il record non sono disponibili.
     */
    private function checklistOr404(?string $uuid): object
    {
        if ($uuid === null || ! $this->validateData(['uuid' => $uuid], $this->checklistsModel->delValidationRules())): throw PageNotFoundException::forPageNotFound(); endif;
        $record = $this->checklistsModel->getByUUID($uuid);
        if ( ! $record['result']): throw PageNotFoundException::forPageNotFound(); endif;
        return $record['row'];
    }

    /**
     * Usa la stessa decisione dei permessi impiegata dal backbone e dai registrar.
     * @param string $permission Permesso applicativo richiesto.
     * @return bool True quando l'amministratore è autorizzato.
     */
    private function allows(string $permission): bool
    {
        return AuthorizationClass::allows($this->currentAdmin, $permission);
    }

    /**
     * Restituisce un rifiuto senza dettagli dei record non autorizzati.
     * @return ResponseInterface JSON HTTP 403.
     */
    private function forbidden(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/checklists.messages.forbidden')])->setStatusCode(403);
    }

    /**
     * Converte gli errori nativi del controller nel formato AJAX comune.
     * @param string $context form, modal oppure action: contesto interno della risposta.
     * @return ResponseInterface Esito JSON non salvabile.
     */
    private function validationResponse(string $context = 'form'): ResponseInterface
    {
        return $this->jsonResponse($this->withValidationMessage(['result' => false, 'errors' => $this->validator->getErrors()], $context));
    }

    /**
     * Registra operazioni già completate senza usare identità o dati di audit ricevuti dal client.
     * @param string $action Identificatore dell'azione.
     * @param string $uuid Identificativo del record.
     * @param string $name Nome del record da registrare come testo sicuro.
     * @param string $section Modulo applicativo interessato.
     * @return void
     */
    private function audit(string $action, string $uuid, string $name, string $section = 'checklists'): void
    {
        log_admin_activity($action, $section, esc($name) . ' [' . esc($uuid) . ']', $this->currentAdmin);
    }

    /**
     * @return string|ResponseInterface Form vuoto dopo il salvataggio, senza redirect.
     */
    public function add(): string|ResponseInterface
    {
        if ( ! $this->allows('checklists_add')): return $this->forbidden(); endif;
        $this->data['checklist'] = null; $this->prepareView('add');
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            if (($posts['action'] ?? null) === 'reset'): return $this->jsonResponse(['result' => true, 'output' => view(Views::ADD_PARTIAL, $this->data), 'navigationCounts' => $this->data['navigationCounts']]); endif;
            $result = $this->checklistsModel->add($posts, $this->currentAdmin->uuid ?? null);
            if ($result['result']): $this->audit('ADD_CHECKLISTS', $result['uuid'], $posts['name']); endif;
            return $this->jsonResponse($this->withValidationMessage($result));
        endif;
        return $this->render(Views::ADD_VIEW, $this->data);
    }

    /**
     * @return ResponseInterface Copie dei soli titoli: nessun UUID del modello entra nei passi del task.
     */
    public function getItems(): ResponseInterface
    {
        if ( ! $this->allows('checklists_apply') || ! ($this->allows('tasks_add') || $this->allows('tasks_edit'))): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if (array_diff(array_keys($posts), ['uuid']) || ! $this->validateData($posts, $this->checklistsModel->delValidationRules())): return $this->jsonResponse(['result' => false, 'message' => lang('backend/checklists.messages.invalidData')]); endif;
        $record = $this->checklistsModel->getByUUID($posts['uuid']);
        if ( ! $record['result'] || $record['row']->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/checklists.messages.notFound')]); endif;
        return $this->jsonResponse(['result' => true, 'steps' => array_map(static fn(object $step): array => ['title' => $step->title], $record['row']->steps)]);
    }

    /**
     * @return ResponseInterface Pannello generale, dopo validazione di UUID e contesto.
     */
    public function getGeneralData(): ResponseInterface
    {
        $posts = $this->request->getPost();
        $rules = $this->checklistsModel->delValidationRules() + ['context' => ['rules' => ['required', 'string', 'in_list[edit,show]']]];
        if ( ! $this->validateData($posts, $rules)): return $this->validationResponse('action'); endif;
        if ( ! $this->allows($posts['context'] === 'edit' ? 'checklists_edit' : 'checklists_show')): return $this->forbidden(); endif;
        $this->data['checklist'] = $this->checklistOr404($posts['uuid']);
        $this->prepareView($posts['context']);
        return $this->jsonResponse(['result' => true, 'output' => view($posts['context'] === 'edit' ? Views::EDIT_GENERAL_DATA_PARTIAL : Views::SHOW_GENERAL_DATA_PARTIAL, $this->data)]);
    }

    /**
     * @param string $action Azione della vista.
     * @return void Dati reali per layout, menu e form.
     */
    private function prepareView(string $action): void
    {
        $this->data['action'] = $action; $this->data['title'] = lang('backend/checklists.titles.' . $action);
        $this->data['icon'] = '<i class="fa-solid fa-clipboard-check"></i>'; $this->data['options'] = '';
        $this->data['uuid'] = $this->data['checklist']->uuid ?? null;
        $this->data['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin);
    }

    /**
     * @param array $result Esito.
     * @param string $context form oppure action.
     * @return array Alert generico nei form, dettagli nelle azioni senza campi visibili.
     */
    private function withValidationMessage(array $result, string $context = 'form'): array
    {
        if ( ! isset($result['errors'])): return $result; endif;
        if ($context === 'action'):
            $result['message'] = implode('<br>', array_map(static fn($error): string => esc((string) $error), $result['errors']));
        else:
            $fields = [];
            foreach ($result['errors'] as $field => $error):
                if (preg_match('/^steps\.(\d+)\.title$/', (string) $field, $match)): $key = 'step_' . $match[1] . '_title';
                elseif (str_starts_with((string) $field, 'steps.')): $key = 'steps';
                else: $key = preg_replace('/^(searchFields|searchDates)\./', '', (string) $field); endif;
                $fields[$key] = $error;
            endforeach;
            $result['errors'] = $fields; $result['message'] = lang('backend/checklists.messages.validationErrors');
        endif;
        return $result;
    }
}
