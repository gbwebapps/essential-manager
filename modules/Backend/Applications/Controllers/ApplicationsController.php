<?php declare(strict_types = 1);

namespace Modules\Backend\Applications\Controllers;

use App\Controllers\Backend\BackendController;
use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Applications\Config\Views;
use Modules\Backend\Applications\Libraries\ApplicationsClass;
use Modules\Backend\Applications\Models\ApplicationsModel;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Notes\Models\NotesModel;
use Psr\Log\LoggerInterface;

/** Gestisce applicazioni reali, bozza iniziale e modali dei dati collegati. */
class ApplicationsController extends BackendController
{
    protected ApplicationsModel $applicationsModel;
    protected ApplicationsClass $applicationsClass;
    protected TasksModel $tasksModel;
    protected NotesModel $notesModel;

    /**
     * Inizializza i modelli applicativi mantenendo autenticazione e layout nel backbone.
     * @param RequestInterface $request Richiesta HTTP corrente.
     * @param ResponseInterface $response Risposta HTTP corrente.
     * @param LoggerInterface $logger Logger applicativo.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->data['controller'] = 'applications';
        $this->data['entity'] = 'applications';
        $this->data['application'] = null;
        $this->applicationsModel = model(ApplicationsModel::class);
        $this->tasksModel = model(TasksModel::class);
        $this->notesModel = model(NotesModel::class);
        $this->applicationsClass = new ApplicationsClass();
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
     * Valida i filtri e delega paginazione e query al BackendModel tramite ApplicationsModel.
     * @return string|ResponseInterface Lista HTML oppure JSON con record e conteggi aggiornati.
     */
    public function showAll(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $validated = $this->applicationsModel->validateList($this->request->getPost());
            if ( ! $validated['result']): return $this->jsonResponse($this->withValidationMessage($validated)); endif;
            $this->data['posts'] = $validated['data'];
            $this->data['data'] = $this->applicationsModel->getData($validated['data']);
            if ( ! $this->data['data']['result']): return $this->jsonResponse($this->data['data']); endif;
            $this->prepareView('showAll');
            return $this->jsonResponse([
                'result' => true,
                'output' => view(Views::SHOW_ALL_PARTIAL, $this->data),
                'trashCounts' => $this->applicationsModel->getTrashCounts(),
                'navigationCounts' => $this->data['navigationCounts'],
            ]);
        endif;
        $this->prepareView('showAll');
        return $this->render(Views::SHOW_ALL_VIEW, $this->data);
    }

    /**
     * Mostra il form o salva applicazione e bozza in un'unica transazione validata dal model.
     * @return string|ResponseInterface HTML di inserimento, reset oppure risultato con UUID persistito.
     */
    public function add(): string|ResponseInterface
    {
        $this->data['application'] = null;
        $this->prepareView('add');
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            if ( ! $this->allows('applications_add')): return $this->forbidden(); endif;
            if (($posts['action'] ?? null) === 'reset'):
                return $this->jsonResponse(['result' => true, 'output' => view(Views::ADD_PARTIAL, $this->data)]);
            endif;
            if (( ! empty($posts['tasks']) && ! $this->allows('tasks_add')) || ( ! empty($posts['notes']) && ! $this->allows('notes_add'))): return $this->forbidden(); endif;
            if (is_array($posts['tasks'] ?? null) && ! $this->allows('tasks_complete')):
                foreach ($posts['tasks'] as $task):
                    if (is_array($task) && ($task['status'] ?? null) === 'completed'): return $this->forbidden(); endif;
                endforeach;
            endif;
            $result = $this->applicationsModel->add($posts, $this->currentAdmin->uuid ?? null);
            if ($result['result']): $this->audit('ADD_APPLICATIONS', $result['uuid'], $result['row']->name); endif;
            unset($result['row']);
            return $this->jsonResponse($this->withValidationMessage($result));
        endif;
        return $this->render(Views::ADD_VIEW, $this->data);
    }

    /**
     * Carica, ricarica o aggiorna l’applicazione corrente, senza accettare modifiche a campi di sistema.
     * @param string|null $uuid UUID nella rotta GET; nelle POST proviene dal form e viene validato.
     * @return string|ResponseInterface HTML di modifica oppure esito JSON.
     */
    public function edit(?string $uuid = null): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            if ( ! $this->allows('applications_edit')): return $this->forbidden(); endif;
            if ( ! $this->validateData($posts, $this->applicationsModel->delValidationRules())): return $this->validationResponse(); endif;
            $application = $this->applicationOr404($posts['uuid']);
            if ($application->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/applications.messages.cannotModifyDeleted')]); endif;
            if (($posts['action'] ?? null) === 'refresh'):
                $this->data['application'] = $application;
                $this->prepareView('edit');
                return $this->jsonResponse(['result' => true, 'output' => view(Views::EDIT_PARTIAL, $this->data)]);
            endif;
            $result = $this->applicationsModel->edit($posts);
            if ($result['result']): $this->audit('EDIT_APPLICATIONS', $posts['uuid'], $result['row']->name); endif;
            unset($result['row']);
            return $this->jsonResponse($this->withValidationMessage($result));
        endif;
        $this->data['application'] = $this->applicationOr404($uuid);
        if ($this->data['application']->deleted_at !== null): throw PageNotFoundException::forPageNotFound(); endif;
        $this->prepareView('edit');
        return $this->render(Views::EDIT_VIEW, $this->data);
    }

    /**
     * Mostra il dettaglio di un’applicazione attiva e i dati collegati in sola lettura.
     * @param string $uuid Identificativo nella rotta.
     * @return string HTML del dettaglio.
     */
    public function show(string $uuid): string
    {
        $this->data['application'] = $this->applicationOr404($uuid);
        if ($this->data['application']->deleted_at !== null): throw PageNotFoundException::forPageNotFound(); endif;
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
     * Esegue lo spostamento nel cestino dell’applicazione.
     * @return ResponseInterface Esito JSON.
     */
    public function softDelete(): ResponseInterface
    {
        return $this->changeDeletion('softDelete');
    }

    /**
     * Ripristina un’applicazione cestinata.
     * @return ResponseInterface Esito JSON.
     */
    public function restoreDelete(): ResponseInterface
    {
        return $this->changeDeletion('restoreDelete');
    }

    /**
     * Ricarica il pannello generale usando solo i due contesti previsti.
     * @return ResponseInterface HTML parziale in JSON oppure errori di validazione.
     */
    public function getGeneralData(): ResponseInterface
    {
        $posts = $this->request->getPost();
        $rules = $this->applicationsModel->delValidationRules() + ['context' => ['rules' => ['required', 'string', 'in_list[edit,show]']]];
        if ( ! $this->validateData($posts, $rules)): return $this->validationResponse('action'); endif;
        $this->data['application'] = $this->applicationOr404($posts['uuid']);
        if ($this->data['application']->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/applications.messages.cannotModifyDeleted')]); endif;
        $this->data['projectOptions'] = $this->applicationsModel->getProjectOptions();
        return $this->jsonResponse(['result' => true, 'output' => view($posts['context'] === 'edit' ? Views::EDIT_GENERAL_DATA_PARTIAL : Views::SHOW_GENERAL_DATA_PARTIAL, $this->data)]);
    }

    /**
     * Rilegge le date dell’applicazione dal database senza utilizzare metadati inviati dal browser.
     * @return ResponseInterface HTML dei metadati in JSON.
     */
    public function getMetaData(): ResponseInterface
    {
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->applicationsModel->delValidationRules())): return $this->validationResponse('action'); endif;
        $this->data['application'] = $this->applicationOr404($posts['uuid']);
        return $this->jsonResponse(['result' => true, 'output' => view(Views::META_DATA_PARTIAL, $this->data)]);
    }

    /**
     * Ricarica i task reali dell’applicazione verificando anche il permesso del modulo Tasks.
     * @return ResponseInterface Elenco HTML in JSON.
     */
    public function getTasks(): ResponseInterface
    {
        return $this->getRelatedData('task');
    }

    /**
     * Ricarica le note reali dell’applicazione verificando il permesso Notes.
     * @return ResponseInterface Elenco HTML in JSON.
     */
    public function getNotes(): ResponseInterface
    {
        return $this->getRelatedData('note');
    }

    /**
     * Salva task e passi del modale nell’applicazione autorizzato.
     * @return ResponseInterface Record persistito oppure errori, senza chiudere il modale.
     */
    public function saveTask(): ResponseInterface
    {
        return $this->saveRelated('task');
    }

    /**
     * Salva una nota dell’applicazione autorizzato.
     * @return ResponseInterface Record persistito oppure errori.
     */
    public function saveNote(): ResponseInterface
    {
        return $this->saveRelated('note');
    }

    /**
     * Valida UUID e contesto dei pannelli, mantenendo la sola lettura dopo la ricarica di show.
     * @param string $kind Tipo interno task oppure note.
     * @return ResponseInterface Risposta JSON del pannello richiesto.
     */
    private function getRelatedData(string $kind): ResponseInterface
    {
        if ( ! $this->allowsApplicationContext()): return $this->forbidden(); endif;
        if ( ! $this->allows($kind === 'task' ? 'tasks_show' : 'notes_show')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        $rules = $this->applicationsModel->delValidationRules() + ['context' => ['rules' => ['required', 'string', 'in_list[edit,show]']]];
        if ( ! $this->validateData($posts, $rules)): return $this->validationResponse('action'); endif;
        $application = $this->applicationOr404($posts['uuid']);
        if ($application->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/applications.messages.cannotModifyDeleted')]); endif;
        return $this->jsonResponse(['result' => true, 'output' => view(Views::RELATED_ITEMS_PARTIAL, [
            'relatedKind' => $kind,
            'relatedReadOnly' => $posts['context'] === 'show',
            'relatedItems' => $kind === 'task' ? $this->tasksModel->getForApplication($application->uuid) : $this->notesModel->getForApplication($application->uuid),
            'relatedApplication' => $application,
            'currentAdmin' => $this->currentAdmin,
        ])]);
    }

    /**
     * Valida un task della bozza senza scrivere su database o sessione.
     * @return ResponseInterface Dati normalizzati o errori del model, in JSON.
     */
    public function validateTaskDraft(): ResponseInterface
    {
        return $this->validateDraft('task');
    }

    /**
     * Valida una nota della bozza senza scrivere su database o sessione.
     * @return ResponseInterface Contenuto normalizzato o errori del model, in JSON.
     */
    public function validateNoteDraft(): ResponseInterface
    {
        return $this->validateDraft('note');
    }

    /**
     * Applica alle bozze le stesse regole del salvataggio definitivo e verifica i permessi.
     * @param string $kind Tipo interno task oppure note.
     * @return ResponseInterface JSON; nessuna creazione di record o identificativi persistenti.
     */
    private function validateDraft(string $kind): ResponseInterface
    {
        $module = $kind === 'task' ? 'tasks' : 'notes';
        if ( ! $this->allows('applications_add') || ! $this->allows($module . '_add')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if ($kind === 'task' && ($posts['status'] ?? null) === 'completed' && ! $this->allows('tasks_complete')): return $this->forbidden(); endif;
        $model = $kind === 'task' ? $this->tasksModel : $this->notesModel;
        return $this->jsonResponse($this->withValidationMessage($model->validatePayload($posts), 'modal'));
    }

    /**
     * Controlla il permesso add/edit corretto e assegna il contesto al model dal lato server.
     * @param string $kind Tipo interno task oppure note.
     * @return ResponseInterface Esito del salvataggio del modale.
     */
    private function saveRelated(string $kind): ResponseInterface
    {
        if ( ! $this->allowsApplicationContext()): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        $rules = ['application_uuid' => $this->applicationsModel->delValidationRules()['uuid'], 'uuid' => ['rules' => ['permit_empty', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']]];
        if ((array_key_exists('uuid', $posts) && ! is_string($posts['uuid'])) || ! $this->validateData($posts, $rules)): return $this->jsonResponse(['result' => false, 'message' => lang('backend/applications.messages.invalidData')]); endif;
        $editing = ($posts['uuid'] ?? '') !== '';
        $module = $kind === 'task' ? 'tasks' : 'notes';
        if ( ! $this->allows($module . ($editing ? '_edit' : '_add'))): return $this->forbidden(); endif;
        $applicationUuid = $posts['application_uuid'];
        unset($posts['application_uuid']);
        $result = $kind === 'task'
            ? $this->tasksModel->saveForApplication($posts, $applicationUuid, $this->currentAdmin->uuid ?? null, $this->allows('tasks_complete'))
            : $this->notesModel->saveForApplication($posts, $applicationUuid, $this->currentAdmin->uuid ?? null);
        if ($result['result']): $this->audit(($editing ? 'EDIT_' : 'ADD_') . strtoupper($module), $result['record']->uuid, $kind === 'task' ? $result['record']->title : lang('backend/applications.related.notes'), $module); endif;
        return $this->jsonResponse($this->withValidationMessage($result, 'modal'));
    }

    /**
     * Esegue una transizione del cestino validata e ne registra l'audit dopo il commit.
     * @param string $method Metodo interno del model.
     * @return ResponseInterface Risposta JSON.
     */
    private function changeDeletion(string $method): ResponseInterface
    {
        if ( ! $this->allows($method === 'restoreDelete' ? 'applications_restore' : 'applications_delete')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->applicationsModel->delValidationRules())): return $this->validationResponse('action'); endif;
        $result = $this->applicationsModel->{$method}($posts);
        if ($result['result']): $this->audit(strtoupper($method) . '_APPLICATIONS', $result['uuid'], $result['name']); endif;
        return $this->jsonResponse($result);
    }

    /**
     * Prepara dati, origini e conteggi reali per la vista richiesta.
     * @param string $action Azione corrente.
     * @return void
     */
    private function prepareView(string $action): void
    {
        $this->data['action'] = $action;
        $this->data['draftMode'] = $action === 'add';
        $this->data['title'] = lang('backend/applications.titles.' . $action);
        $this->data['icon'] = '<i class="fa-solid fa-desktop"></i>';
        $this->data['options'] = '';
        $application = $this->data['application'] ?? null;
        $this->data['uuid'] = $application->uuid ?? null;
        $selectedProject = $this->request->getGet('project_uuid');
        $this->data['selectedProjectUuid'] = is_string($selectedProject) && $this->validateData(['uuid' => $selectedProject], $this->applicationsModel->delValidationRules()) ? $selectedProject : '';

        $this->data['projectOptions'] = $this->applicationsModel->getProjectOptions();
        $this->data['navigationCounts'] = ['applications' => $this->applicationsModel->activeCount()];
        $this->data['applicationTasks'] = $application && $this->allows('tasks_show') ? $this->tasksModel->getForApplication($application->uuid) : [];
        $this->data['applicationNotes'] = $application && $this->allows('notes_show') ? $this->notesModel->getForApplication($application->uuid) : [];
    }

    /**
     * Recupera un’applicazione con il metodo globale getByUUID oppure restituisce 404.
     * @param string|null $uuid UUID ricevuto dalla rotta o già validato dalla POST.
     * @return object Applicazione persistita con i conteggi.
     * @throws PageNotFoundException Se l'identificativo o il record non sono disponibili.
     */
    private function applicationOr404(?string $uuid): object
    {
        if ($uuid === null || ! $this->validateData(['uuid' => $uuid], $this->applicationsModel->delValidationRules())): throw PageNotFoundException::forPageNotFound(); endif;
        $record = $this->applicationsModel->getByUUID($uuid);
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
     * Richiede accesso all’applicazione anche quando il chiamante possiede permessi sui suoi task o note.
     * @return bool True se il contesto dell’applicazione è accessibile.
     */
    private function allowsApplicationContext(): bool
    {
        return $this->allows('applications_show_all') || $this->allows('applications_show') || $this->allows('applications_edit');
    }

    /**
     * Restituisce un rifiuto senza dettagli dei record non autorizzati.
     * @return ResponseInterface JSON HTTP 403.
     */
    private function forbidden(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/applications.messages.forbidden')])->setStatusCode(403);
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
     * Distingue errori di campi visibili e azioni prive di campi modificabili, come in Admins.
     * @param array $result Esito con eventuali errori del model o del controller.
     * @param string $context form per campi principali, modal per passi annidati, action per parametri nascosti.
     * @return array Esito con alert generico nei form oppure dettagli escapati nelle azioni.
     */
    private function withValidationMessage(array $result, string $context = 'form'): array
    {
        if ( ! isset($result['errors'])): return $result; endif;
        if ($context === 'action'):
            $result['message'] = implode('<br>', array_map(static fn ($error): string => esc((string) $error), $result['errors']));
            return $result;
        endif;
        if ($context === 'form'):
            $fields = [];
            foreach ($result['errors'] as $field => $error):
                $parts = explode('.', (string) $field);
                $key = in_array($parts[0], ['searchFields', 'searchDates'], true) ? ($parts[1] ?? 'payload') : $parts[0];
                $fields[$key] = isset($fields[$key]) ? $fields[$key] . ' ' . $error : $error;
            endforeach;
            $result['errors'] = $fields;
        endif;
        $result['message'] = lang('backend/applications.messages.validationErrors');
        return $result;
    }


    /**
     * Registra operazioni già completate senza usare identità o dati di audit ricevuti dal client.
     * @param string $action Identificatore dell'azione.
     * @param string $uuid Identificativo del record.
     * @param string $name Nome del record da registrare come testo sicuro.
     * @param string $section Modulo applicativo interessato.
     * @return void
     */
    private function audit(string $action, string $uuid, string $name, string $section = 'applications'): void
    {
        log_admin_activity($action, $section, esc($name) . ' [' . esc($uuid) . ']', $this->currentAdmin);
    }
}
