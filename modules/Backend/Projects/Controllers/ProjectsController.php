<?php declare(strict_types = 1);

namespace Modules\Backend\Projects\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\RedirectResponse;
use Modules\Backend\Projects\Config\Views;
use Modules\Backend\Projects\Models\ProjectsModel;
use Modules\Backend\Projects\Libraries\ProjectsClass;
use App\Controllers\Backend\BackendController;

/**
 * Anteprima delle viste Projects con dati fittizi, senza salvataggi.
 */
class ProjectsController extends BackendController
{
    protected ProjectsModel $projectsModel;

    protected ProjectsClass $projectsClass;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'projects';
        $this->data['entity'] = 'projects';

        $this->projectsModel = model(ProjectsModel::class);
        $this->projectsClass = new ProjectsClass();

        $this->data['parentProjectOptions'] = array_values(array_filter($this->getDummyProjects(), static fn (object $project): bool => $project->deleted_at === null));
    }

    public function index(): string
    {
        $this->prepareDummyView('showAll');

        return $this->render(Views::SHOW_ALL_VIEW, $this->data);
    }

    public function showAll(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            $posts = $this->request->getPost();
            $list = $this->getDummyList($posts);
            $this->data['posts'] = $list['posts'];
            $this->data['data'] = $list['data'];

            $projects = $this->getDummyProjects();
            $trashed = count(array_filter($projects, static fn (object $project): bool => $project->deleted_at !== null));

            return $this->jsonResponse([
                'result' => true,
                'output' => view(Views::SHOW_ALL_PARTIAL, $this->data),
                'trashCounts' => ['active' => count($projects) - $trashed, 'trashed' => $trashed, 'all' => count($projects)],
            ]);
        endif;

        $this->prepareDummyView('showAll');

        return $this->render(Views::SHOW_ALL_VIEW, $this->data);
    }

    public function add(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            if ($this->request->getPost('action') === 'reset'):
                return $this->jsonResponse(['result' => true, 'output' => view(Views::ADD_PARTIAL, $this->data)]);
            endif;

            return $this->previewOnly();
        endif;

        $this->prepareDummyView('add');

        return $this->render(Views::ADD_VIEW, $this->data);
    }

    public function edit(?string $uuid = null): string|ResponseInterface
    {
        if ($this->request->is('post')):
            if ($this->request->getPost('action') !== 'refresh'):
                return $this->previewOnly();
            endif;

            $postedUuid = $this->request->getPost('uuid');
            $this->data['project'] = $this->getDummyProject(is_string($postedUuid) ? $postedUuid : null);
            $this->data['uuid'] = $this->data['project']->uuid;

            return $this->jsonResponse(['result' => true, 'output' => view(Views::EDIT_PARTIAL, $this->data)]);
        endif;

        $this->data['project'] = $this->getDummyProject($uuid);
        $this->data['uuid'] = $this->data['project']->uuid;
        $this->prepareDummyView('edit');

        return $this->render(Views::EDIT_VIEW, $this->data);
    }

    public function show(string $uuid): RedirectResponse|string
    {
        $this->data['project'] = $this->getDummyProject($uuid);
        $this->data['uuid'] = $this->data['project']->uuid;
        $this->prepareDummyView('show');

        return $this->render(Views::SHOW_VIEW, $this->data);
    }

    public function hardDelete(): ResponseInterface
    {
        return $this->previewOnly();
    }

    public function softDelete(): ResponseInterface
    {
        return $this->previewOnly();
    }

    public function restoreDelete(): ResponseInterface
    {
        return $this->previewOnly();
    }

    public function getGeneralData(): ResponseInterface
    {
        $uuid = $this->request->getPost('uuid');
        $this->data['project'] = $this->getDummyProject(is_string($uuid) ? $uuid : null);
        $view = $this->request->getPost('context') === 'edit' ? Views::EDIT_GENERAL_DATA_PARTIAL : Views::SHOW_GENERAL_DATA_PARTIAL;

        return $this->jsonResponse(['result' => true, 'output' => view($view, $this->data)]);
    }

    public function getMetaData(): ResponseInterface
    {
        $uuid = $this->request->getPost('uuid');
        $this->data['project'] = $this->getDummyProject(is_string($uuid) ? $uuid : null);

        return $this->jsonResponse(['result' => true, 'output' => view(Views::META_DATA_PARTIAL, $this->data)]);
    }

    private function prepareDummyView(string $action): void
    {
        $this->data['action'] = $action;
        $this->data['title'] = lang('backend/projects.titles.' . $action);
        $this->data['icon'] = '<i class="fa-solid fa-folder-open"></i>';
        $this->data['options'] = '<span class="badge app-bg-muted-subtle app-text-muted-emphasis border text-wrap"><i class="fa-solid fa-circle-info me-1"></i>' . ui_text('backend/projects.messages.previewOnly', [], true) . '</span>';
    }

    private function previewOnly(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/projects.messages.previewOnly')]);
    }

    /** Cerca soltanto fra i dati fittizi del controller. */
    private function getDummyProject(?string $uuid): object
    {
        foreach ($this->getDummyProjects() as $project):
            if ($project->uuid === $uuid):
                return $project;
            endif;
        endforeach;

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    /** Dati temporanei per vedere le pagine; non provengono dal database. */
    private function getDummyProjects(): array
    {
        $base = [
            'id' => 1,
            'uuid' => '56f74671-a310-4200-bf26-f053ff5b9f27',
            'name' => 'Essential Framework',
            'description' => 'Laboratorio del backbone Essential.',
            'parent_project_uuid' => null,
            'parent_project_name' => null,
            'local_path' => 'D:\\Laragon\\www\\gbwebapps\\essential-framework',
            'local_url' => 'https://gbwebapps.test/essential-framework/',
            'production_url' => null,
            'execute_name' => 'essential-framework',
            'is_protected' => 1,
            'created_at' => '2026-11-17 09:00:00',
            'updated_at' => null,
            'deleted_at' => null,
            'restored_at' => null,
        ];

        return [
            (object) $base,
            (object) array_replace($base, [
                'id' => 2, 'uuid' => '0931da61-d2db-47bf-9f07-2254db9d6603',
                'name' => 'GB Web Apps', 'description' => 'Organizzazione e attivita dello studio.',
                'local_path' => 'D:\\Laragon\\www\\gbwebapps', 'local_url' => 'https://gbwebapps.test/',
                'production_url' => 'https://example.com/', 'execute_name' => null,
            ]),
            (object) array_replace($base, [
                'id' => 3, 'uuid' => '7d7fdb48-f47d-4ac8-a3b5-499fdc4989e1',
                'name' => 'Essential Store', 'description' => 'Progetto applicativo per e-commerce.',
                'parent_project_uuid' => $base['uuid'], 'parent_project_name' => $base['name'],
                'local_path' => 'D:\\Laragon\\www\\gbwebapps\\essential-store',
                'local_url' => 'https://gbwebapps.test/essential-store/', 'execute_name' => 'essential-store',
                'is_protected' => 0, 'updated_at' => '2026-11-17 10:00:00',
            ]),
            (object) array_replace($base, [
                'id' => 4, 'uuid' => '06a02a25-8579-4c96-9ccc-8277286566dc',
                'name' => 'Essential Manager', 'description' => 'Gestione del lavoro dello studio.',
                'parent_project_uuid' => $base['uuid'], 'parent_project_name' => $base['name'],
                'local_path' => 'D:\\Laragon\\www\\gbwebapps\\essential-manager',
                'local_url' => 'https://gbwebapps.test/essential-manager/', 'execute_name' => 'essential-manager',
            ]),
            (object) array_replace($base, [
                'id' => 5, 'uuid' => 'f0047a50-b930-40ab-939a-5c80d9e69679',
                'name' => 'Progetto di esempio', 'description' => 'Esempio di progetto nel cestino.',
                'local_path' => null, 'local_url' => null, 'execute_name' => null,
                'is_protected' => 0, 'deleted_at' => '2026-11-17 11:00:00',
            ]),
        ];
    }

    /** Filtri e paginazione della sola anteprima in memoria. */
    private function getDummyList(array $posts): array
    {
        $projects = $this->getDummyProjects();
        $trashFilter = in_array($posts['trash_filter'] ?? null, ['active', 'trashed', 'all'], true) ? $posts['trash_filter'] : 'active';
        $searchFields = is_array($posts['searchFields'] ?? null) ? $posts['searchFields'] : [];
        $searchDates = is_array($posts['searchDates'] ?? null) ? $posts['searchDates'] : [];
        $name = is_string($searchFields['name'] ?? null) ? trim($searchFields['name']) : '';

        $projects = array_values(array_filter($projects, static function (object $project) use ($trashFilter, $name, $searchDates): bool {
            if (($trashFilter === 'active' && $project->deleted_at !== null) || ($trashFilter === 'trashed' && $project->deleted_at === null)):
                return false;
            endif;
            if ($name !== '' && mb_stripos($project->name, $name) === false):
                return false;
            endif;
            foreach (['created_at', 'updated_at'] as $field):
                foreach (['from', 'to'] as $side):
                    $date = $searchDates[$field . '-' . $side] ?? '';
                    if ( ! is_string($date) || $date === ''):
                        continue;
                    endif;
                    if ( ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)):
                        return false;
                    endif;
                    if ($project->{$field} === null || ($side === 'from' && $project->{$field} < $date) || ($side === 'to' && $project->{$field} > $date)):
                        return false;
                    endif;
                endforeach;
            endforeach;
            return true;
        }));

        $column = in_array($posts['column'] ?? null, ['id', 'name'], true) ? $posts['column'] : 'id';
        $order = ($posts['order'] ?? null) === 'asc' ? 'asc' : 'desc';
        usort($projects, static function (object $left, object $right) use ($column, $order): int {
            $comparison = $column === 'id' ? $left->id <=> $right->id : strnatcasecmp($left->name, $right->name);
            return $order === 'asc' ? $comparison : -$comparison;
        });
        $limit = filter_var($posts['rows'] ?? 5, FILTER_VALIDATE_INT);
        $limit = in_array($limit, [5, 10, 15, 20], true) ? $limit : 5;
        $total = count($projects);
        $page = max(1, (int) filter_var($posts['page'] ?? 1, FILTER_VALIDATE_INT));
        $page = min($page, max(1, (int) ceil($total / $limit)));
        $offset = ($page - 1) * $limit;
        $records = array_slice($projects, $offset, $limit);

        return [
            'posts' => ['column' => $column, 'order' => $order, 'page' => $page, 'rows' => $limit, 'trash_filter' => $trashFilter],
            'data' => [
                'records' => $records,
                'lastItemPage' => count($records),
                'pagination' => ['totalRows' => $total, 'limit' => $limit, 'page' => $page, 'offset' => $offset],
            ],
        ];
    }
}
