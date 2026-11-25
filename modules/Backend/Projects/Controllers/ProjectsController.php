<?php declare(strict_types = 1);

namespace Modules\Backend\Projects\Controllers;

use App\Controllers\Backend\BackendController;
use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Projects\Config\Views;
use Modules\Backend\Projects\Libraries\ProjectsClass;
use Modules\Backend\Projects\Models\ProjectsModel;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Notes\Models\NotesModel;
use Psr\Log\LoggerInterface;

/** Gestisce progetti reali, bozza iniziale e modali dei dati collegati. */
class ProjectsController extends BackendController
{
    protected ProjectsModel $projectsModel;
    protected ProjectsClass $projectsClass;
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
        $this->data['controller'] = 'projects';
        $this->data['entity'] = 'projects';
        $this->data['project'] = null;
        $this->projectsModel = model(ProjectsModel::class);
        $this->tasksModel = model(TasksModel::class);
        $this->notesModel = model(NotesModel::class);
        $this->projectsClass = new ProjectsClass();
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
     * Valida i filtri e delega paginazione e query al BackendModel tramite ProjectsModel.
     * @return string|ResponseInterface Lista HTML oppure JSON con record e conteggi aggiornati.
     */
    public function showAll(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $validated = $this->projectsModel->validateList($this->request->getPost());
            if ( ! $validated['result']): return $this->jsonResponse($this->withValidationMessage($validated)); endif;
            $this->data['posts'] = $validated['data'];
            $this->data['data'] = $this->projectsModel->getData($validated['data']);
            if ( ! $this->data['data']['result']): return $this->jsonResponse($this->data['data']); endif;
            $this->prepareView('showAll');
            return $this->jsonResponse([
                'result' => true,
                'output' => view(Views::SHOW_ALL_PARTIAL, $this->data),
                'trashCounts' => $this->projectsModel->getTrashCounts(),
                'navigationCounts' => $this->data['navigationCounts'],
            ]);
        endif;
        $this->prepareView('showAll');
        return $this->render(Views::SHOW_ALL_VIEW, $this->data);
    }

    /**
     * Mostra il form o salva progetto e bozza in un'unica transazione validata dal model.
     * @return string|ResponseInterface HTML di inserimento, reset oppure risultato con UUID persistito.
     */
    public function add(): string|ResponseInterface
    {
        $this->data['project'] = null;
        $this->prepareView('add');
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            if ( ! $this->allows('projects_add')): return $this->forbidden(); endif;
            if (($posts['action'] ?? null) === 'reset'):
                return $this->jsonResponse(['result' => true, 'output' => view(Views::ADD_PARTIAL, $this->data), 'navigationCounts' => $this->data['navigationCounts']]);
            endif;
            if (( ! empty($posts['tasks']) && ! $this->allows('tasks_add')) || ( ! empty($posts['notes']) && ! $this->allows('notes_add'))): return $this->forbidden(); endif;
            if (is_array($posts['tasks'] ?? null) && ! $this->allows('tasks_complete')):
                foreach ($posts['tasks'] as $task):
                    if (is_array($task) && ($task['status'] ?? null) === 'completed'): return $this->forbidden(); endif;
                endforeach;
            endif;
            $result = $this->projectsModel->add($posts, $this->currentAdmin->uuid ?? null, $this->taskNotePermissions());
            if ($result['result']): $this->audit('ADD_PROJECTS', $result['uuid'], $result['row']->name); endif;
            unset($result['row']);
            if ($result['result']): $result['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin); endif;
            return $this->jsonResponse($this->withValidationMessage($result));
        endif;
        return $this->render(Views::ADD_VIEW, $this->data);
    }

    /**
     * Carica, ricarica o aggiorna il progetto corrente, senza accettare modifiche a campi di sistema.
     * @param string|null $uuid UUID nella rotta GET; nelle POST proviene dal form e viene validato.
     * @return string|ResponseInterface HTML di modifica oppure esito JSON.
     */
    public function edit(?string $uuid = null): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            if ( ! $this->allows('projects_edit')): return $this->forbidden(); endif;
            if ( ! $this->validateData($posts, $this->projectsModel->delValidationRules())): return $this->validationResponse(); endif;
            $project = $this->projectOr404($posts['uuid']);
            if ($project->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.cannotModifyDeleted')]); endif;
            if (($posts['action'] ?? null) === 'refresh'):
                $this->data['project'] = $project;
                $this->prepareView('edit');
                return $this->jsonResponse(['result' => true, 'output' => view(Views::EDIT_PARTIAL, $this->data)]);
            endif;
            $result = $this->projectsModel->edit($posts);
            if ($result['result']): $this->audit('EDIT_PROJECTS', $posts['uuid'], $result['row']->name); endif;
            unset($result['row']);
            if ($result['result']): $result['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin); endif;
            return $this->jsonResponse($this->withValidationMessage($result));
        endif;
        $this->data['project'] = $this->projectOr404($uuid);
        if ($this->data['project']->deleted_at !== null): throw PageNotFoundException::forPageNotFound(); endif;
        $this->prepareView('edit');
        return $this->render(Views::EDIT_VIEW, $this->data);
    }

    /**
     * Mostra il dettaglio di un progetto attivo e i dati collegati in sola lettura.
     * @param string $uuid Identificativo nella rotta.
     * @return string HTML del dettaglio.
     */
    public function show(string $uuid): string
    {
        $this->data['project'] = $this->projectOr404($uuid);
        if ($this->data['project']->deleted_at !== null): throw PageNotFoundException::forPageNotFound(); endif;
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
     * Esegue lo spostamento nel cestino del progetto.
     * @return ResponseInterface Esito JSON.
     */
    public function softDelete(): ResponseInterface
    {
        return $this->changeDeletion('softDelete');
    }

    /**
     * Ripristina un progetto cestinato.
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
        $rules = $this->projectsModel->delValidationRules() + ['context' => ['rules' => ['required', 'string', 'in_list[edit,show]']]];
        if ( ! $this->validateData($posts, $rules)): return $this->validationResponse('action'); endif;
        $this->data['project'] = $this->projectOr404($posts['uuid']);
        if ($this->data['project']->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.cannotModifyDeleted')]); endif;
        $this->data['parentProjectOptions'] = $this->projectsModel->getParentOptions($posts['uuid']);
        return $this->jsonResponse(['result' => true, 'output' => view($posts['context'] === 'edit' ? Views::EDIT_GENERAL_DATA_PARTIAL : Views::SHOW_GENERAL_DATA_PARTIAL, $this->data)]);
    }

    /**
     * Rilegge le date del progetto dal database senza utilizzare metadati inviati dal browser.
     * @return ResponseInterface HTML dei metadati in JSON.
     */
    public function getMetaData(): ResponseInterface
    {
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->projectsModel->delValidationRules())): return $this->validationResponse('action'); endif;
        $this->data['project'] = $this->projectOr404($posts['uuid']);
        return $this->jsonResponse(['result' => true, 'output' => view(Views::META_DATA_PARTIAL, $this->data)]);
    }

    /**
     * Ricarica i task reali del progetto verificando anche il permesso del modulo Tasks.
     * @return ResponseInterface Elenco HTML in JSON.
     */
    public function getTasks(): ResponseInterface
    {
        return $this->getRelatedData('task');
    }

    /**
     * Ricarica le note reali del progetto verificando il permesso Notes.
     * @return ResponseInterface Elenco HTML in JSON.
     */
    public function getNotes(): ResponseInterface
    {
        return $this->getRelatedData('note');
    }

    /**
     * Carica task, passi e note del solo proprietario autorizzato per il modale.
     * @return ResponseInterface Record completo oppure errore senza contenuti estranei.
     */
    public function getTaskForModal(): ResponseInterface
    {
        if ( ! $this->allowsProjectContext() || ! $this->allows('tasks_edit')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        $rule = $this->projectsModel->delValidationRules()['uuid'];
        if (array_diff(array_keys($posts), ['uuid', 'project_uuid']) !== [] || ! $this->validateData($posts, ['uuid' => $rule, 'project_uuid' => $rule])):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/tasks.messages.invalidData')]);
        endif;
        $record = model(\Modules\Backend\Tasks\Models\TaskModalModel::class)->getModal($posts['uuid'], 'project_uuid', $posts['project_uuid'], $this->allows('notes_show'));
        return $this->jsonResponse($record ? ['result' => true, 'record' => $record] : ['result' => false, 'message' => lang('backend/tasks.messages.notFound')]);
    }

    /** @return array<string, bool> Permessi delle note stabiliti dall'identità autenticata. */
    private function taskNotePermissions(): array
    {
        return ['show' => $this->allows('notes_show'), 'add' => $this->allows('notes_add'), 'edit' => $this->allows('notes_edit'), 'delete' => $this->allows('notes_delete')];
    }

    /**
     * Salva task e passi del modale nel progetto autorizzato.
     * @return ResponseInterface Record persistito oppure errori, senza chiudere il modale.
     */
    public function saveTask(): ResponseInterface
    {
        return $this->saveRelated('task');
    }

    /**
     * Salva una nota del progetto autorizzato.
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
        if ( ! $this->allowsProjectContext()): return $this->forbidden(); endif;
        if ( ! $this->allows($kind === 'task' ? 'tasks_show' : 'notes_show')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        $rules = $this->projectsModel->delValidationRules() + ['context' => ['rules' => ['required', 'string', 'in_list[edit,show]']]];
        if ( ! $this->validateData($posts, $rules)): return $this->validationResponse('action'); endif;
        $project = $this->projectOr404($posts['uuid']);
        if ($project->deleted_at !== null): return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.cannotModifyDeleted')]); endif;
        return $this->jsonResponse(['result' => true, 'output' => view(Views::RELATED_ITEMS_PARTIAL, [
            'relatedKind' => $kind,
            'relatedReadOnly' => $posts['context'] === 'show',
            'relatedItems' => $kind === 'task' ? $this->tasksModel->getForProject($project->uuid, $this->allows('notes_show')) : $this->notesModel->getForProject($project->uuid),
            'relatedProject' => $project,
            'currentAdmin' => $this->currentAdmin,
        ], ['saveData' => false])]);
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
        if ( ! $this->allows('projects_add') || ! $this->allows($module . '_add')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if ($kind === 'task' && ($posts['status'] ?? null) === 'completed' && ! $this->allows('tasks_complete')): return $this->forbidden(); endif;
        $result = $kind === 'task'
            ? model(\Modules\Backend\Tasks\Models\TaskModalModel::class)->validateDraftPayload($posts, $this->taskNotePermissions())
            : $this->notesModel->validatePayload($posts);
        if ($result['result']):
            $result['previewOutput'] = view(Views::RELATED_ITEMS_PARTIAL, [
                'relatedKind'=>$kind, 'relatedDraft'=>true, 'relatedReadOnly'=>false,
                'relatedItems'=>[\Modules\Backend\Tasks\Libraries\TasksClass::draftRecord($result['data'], $kind)],
                'relatedProject'=>null, 'currentAdmin'=>$this->currentAdmin,
            ], ['saveData'=>false]);
        endif;
        return $this->jsonResponse($this->withValidationMessage($result, 'modal'));
    }

    /**
     * Controlla il permesso add/edit corretto e assegna il contesto al model dal lato server.
     * @param string $kind Tipo interno task oppure note.
     * @return ResponseInterface Esito del salvataggio del modale.
     */
    private function saveRelated(string $kind): ResponseInterface
    {
        if ( ! $this->allowsProjectContext()): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        $rules = ['project_uuid' => $this->projectsModel->delValidationRules()['uuid'], 'uuid' => ['rules' => ['permit_empty', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']]];
        if ((array_key_exists('uuid', $posts) && ! is_string($posts['uuid'])) || ! $this->validateData($posts, $rules)): return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.invalidData')]); endif;
        $editing = ($posts['uuid'] ?? '') !== '';
        $module = $kind === 'task' ? 'tasks' : 'notes';
        if ( ! $this->allows($module . ($editing ? '_edit' : '_add'))): return $this->forbidden(); endif;
        $projectUuid = $posts['project_uuid'];
        unset($posts['project_uuid']);
        $result = $kind === 'task'
            ? model(\Modules\Backend\Tasks\Models\TaskModalModel::class)->saveModal($posts, 'project_uuid', $projectUuid, $this->currentAdmin->uuid ?? null, $this->allows('tasks_complete'), $this->taskNotePermissions())
            : $this->notesModel->saveForProject($posts, $projectUuid, $this->currentAdmin->uuid ?? null);
        if ($result['result']): $this->audit(($editing ? 'EDIT_' : 'ADD_') . strtoupper($module), $result['record']->uuid, $kind === 'task' ? $result['record']->title : lang('backend/projects.related.notes'), $module); endif;
        if ($result['result']): $result['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin); endif;
        return $this->jsonResponse($this->withValidationMessage($result, 'modal'));
    }

    /**
     * Cestina un task dalla scheda di modifica del suo proprietario.
     * @return ResponseInterface Esito JSON con conteggi aggiornati.
     */
    public function deleteTask(): ResponseInterface { return $this->deleteRelated('task'); }

    /**
     * Elimina definitivamente una nota dalla scheda del suo proprietario.
     * @return ResponseInterface Esito JSON senza cancellazioni fuori contesto.
     */
    public function deleteNote(): ResponseInterface { return $this->deleteRelated('note'); }

    /**
     * Verifica permessi e identificativi nascosti prima della cancellazione contestuale.
     * @param string $kind task oppure note, stabilito dal chiamante interno.
     * @return ResponseInterface Esito; errori dei parametri nascosti nell'alert.
     */
    private function deleteRelated(string $kind): ResponseInterface
    {
        $module = $kind === 'task' ? 'tasks' : 'notes';
        if ( ! $this->allows('projects_edit') || ! $this->allows($module . '_delete')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        $rule = $this->projectsModel->delValidationRules()['uuid'];
        if (array_diff(array_keys($posts), ['uuid', 'project_uuid']) !== []): return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.invalidData')]); endif;
        if ( ! $this->validateData($posts, ['uuid' => $rule, 'project_uuid' => $rule])): return $this->validationResponse('action'); endif;
        $model = $kind === 'task' ? $this->tasksModel : $this->notesModel;
        $result = $model->deleteForOwner($posts['uuid'], 'project_uuid', $posts['project_uuid']);
        if ($result['result']):
            $this->audit(($kind === 'task' ? 'SOFTDELETE_' : 'HARDDELETE_') . strtoupper($module), $result['uuid'], $module, $module);
            $result['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin);
        endif;
        return $this->jsonResponse($result);
    }

    /**
     * Esegue una transizione del cestino validata e ne registra l'audit dopo il commit.
     * @param string $method Metodo interno del model.
     * @return ResponseInterface Risposta JSON.
     */
    private function changeDeletion(string $method): ResponseInterface
    {
        if ( ! $this->allows($method === 'restoreDelete' ? 'projects_restore' : 'projects_delete')): return $this->forbidden(); endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->projectsModel->delValidationRules())): return $this->validationResponse('action'); endif;
        $result = $this->projectsModel->{$method}($posts);
        if ($result['result']): $this->audit(strtoupper($method) . '_PROJECTS', $result['uuid'], $result['name']); endif;
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
        $this->data['title'] = lang('backend/projects.titles.' . $action);
        $this->data['icon'] = '<i class="fa-solid fa-folder-open"></i>';
        $this->data['options'] = '';
        $project = $this->data['project'] ?? null;
        $this->data['uuid'] = $project->uuid ?? null;
        $this->data['parentProjectOptions'] = $this->projectsModel->getParentOptions($project->uuid ?? null);
        $this->data['navigationCounts'] = \Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin);
        $this->data['projectApplications'] = $project && $this->allows('applications_show_all') ? model(\Modules\Backend\Applications\Models\ApplicationsModel::class)->getForProject($project->uuid) : [];
        $this->data['projectTasks'] = $project && $this->allows('tasks_show') ? $this->tasksModel->getForProject($project->uuid, $this->allows('notes_show')) : [];
        $this->data['projectNotes'] = $project && $this->allows('notes_show') ? $this->notesModel->getForProject($project->uuid) : [];
    }

    /**
     * Recupera un progetto con il metodo globale getByUUID oppure restituisce 404.
     * @param string|null $uuid UUID ricevuto dalla rotta o già validato dalla POST.
     * @return object Progetto persistito con i conteggi.
     * @throws PageNotFoundException Se l'identificativo o il record non sono disponibili.
     */
    private function projectOr404(?string $uuid): object
    {
        if ($uuid === null || ! $this->validateData(['uuid' => $uuid], $this->projectsModel->delValidationRules())): throw PageNotFoundException::forPageNotFound(); endif;
        $record = $this->projectsModel->getByUUID($uuid);
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
     * Richiede accesso al progetto anche quando il chiamante possiede permessi sui suoi task o note.
     * @return bool True se il contesto del progetto è accessibile.
     */
    private function allowsProjectContext(): bool
    {
        return $this->allows('projects_show_all') || $this->allows('projects_show') || $this->allows('projects_edit');
    }

    /**
     * Restituisce un rifiuto senza dettagli dei record non autorizzati.
     * @return ResponseInterface JSON HTTP 403.
     */
    private function forbidden(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.forbidden')])->setStatusCode(403);
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
        $result['message'] = lang('backend/projects.messages.validationErrors');
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
    private function audit(string $action, string $uuid, string $name, string $section = 'projects'): void
    {
        log_admin_activity($action, $section, esc($name) . ' [' . esc($uuid) . ']', $this->currentAdmin);
    }
}
