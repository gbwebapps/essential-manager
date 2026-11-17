<?php declare(strict_types = 1);

namespace App\Controllers\Backend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use Config\Backend\AdminTransfer as Views;
use Config\Backend\AdminTransfer as Package;
use App\Libraries\Backend\Transfer\PackageXlsx;
use App\Libraries\Backend\Transfer\PackageXlsxImport;
use App\Libraries\Backend\Transfer\PackageXlsxExport;
use App\Libraries\Backend\Transfer\TransferRecoveryException;
use App\Models\Backend\AdminTransferModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Gestisce la sezione dedicata all'importazione e all'esportazione del pacchetto trasferimento amministratori.
 */
class AdminTransferController extends BackendController
{
    protected AdminTransferModel $adminTransferModel;
    protected PackageXlsx $adminTransferXlsx;
    protected PackageXlsxExport $adminTransferXlsxExport;
    protected PackageXlsxImport $adminTransferXlsxImport;

    /**
     * Inizializza le dipendenze e i dati condivisi dal modulo AdminTransfer.
     *
     * @param RequestInterface $request Richiesta HTTP corrente
     * @param ResponseInterface $response Risposta HTTP corrente
     * @param LoggerInterface $logger Sistema di log
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'admintransfer';
        $this->data['entity'] = 'admintransfer';

        $this->adminTransferModel = model(AdminTransferModel::class);
        $this->adminTransferXlsx = new PackageXlsx($this->adminTransferModel);
        $this->adminTransferXlsxExport = new PackageXlsxExport($this->adminTransferModel, $this->adminTransferXlsx);
        $this->adminTransferXlsxImport = new PackageXlsxImport($this->adminTransferModel);
    }

    /**
     * Renderizza la schermata principale del pacchetto trasferimento amministratori.
     *
     * @return string HTML renderizzato della vista
     */
    public function index(): string
    {
        $this->data['action'] = 'index';

        $this->data['title'] = lang('backend/admintransfer.titles.index');
        $this->data['icon'] = '<i class="fa-solid fa-users-gear"></i>';
        $this->data['packageTables'] = $this->adminTransferModel->getPackageTables();
        $lastImportId = $this->session->get('admintransferImportId');
        $this->data['admintransferImport'] = is_string($lastImportId)
            ? $this->adminTransferXlsxImport->getImportStatus($lastImportId)
            : null;

        return $this->render(Views::INDEX_VIEW, $this->data);
    }

    /**
     * Genera il pacchetto XLSX completo del trasferimento amministratori.
     *
     * @return ResponseInterface Risposta JSON con URL protetto di download
     */
    public function export(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        try
        {
            $posts = $this->request->getPost();
            unset($posts['currentPassword']);
            $hasExportId = array_key_exists('exportId', $posts);
            $rules = $hasExportId
                ? ['exportId' => 'required|regex_match[/^[a-f0-9]{64}$/]']
                : ['entity' => 'required|in_list[admintransfer]'];

            if ( ! $this->validateData($posts, $rules) || array_diff(array_keys($posts), array_keys($rules)) !== []):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/components/export.messages.invalidEntity')]);
            endif;

            $passwordError = $hasExportId
                ? $this->requireTransferPassword((string) $posts['exportId'])
                : $this->requireCurrentPassword();
            if ($passwordError !== null):
                return $passwordError;
            endif;

            $export = $this->adminTransferXlsxExport->generate($hasExportId ? (string) $posts['exportId'] : null);
            if (($export['result'] ?? false) && isset($export['exportId'])):
                $this->rememberTransferPassword((string) $export['exportId']);
            endif;

            if ($export['result'] && $export['isFinished'] && ! $export['alreadyCompleted']):
                try
                {
                    log_admin_activity(
                        'EXPORT_ADMIN_TRANSFER', 'admintransfer',
                        ui_message('backend/admintransfer.audits.export', [$export['processedCount']], lang('backend/admintransfer.audits.export')),
                        $this->currentAdmin
                    );
                }
                catch (\Throwable $e)
                {
                    log_message('error', 'Audit export AdminTransfer non riuscito: ' . $e->getMessage());
                }
            endif;

            return $this->jsonResponse($export);
        }
        catch (\DomainException $e)
        {
            return $this->jsonResponse(['result' => false, 'message' => esc($e->getMessage())]);
        }
        catch (\Throwable $e)
        {
            log_message('error', 'Esportazione AdminTransfer XLSX non riuscita: ' . $e->getMessage());

            return $this->jsonResponse([
                'result' => false,
                'message' => lang('backend/admintransfer.messages.exportError'),
            ]);
        }
    }

    /**
     * Scarica un pacchetto XLSX appartenente alla sessione corrente.
     *
     * @param string $exportId Identificatore crittografico dell'esportazione
     * @return ResponseInterface Risposta di download
     * @throws PageNotFoundException Se il file non esiste o appartiene a un'altra sessione
     */
    public function download(string $exportId): ResponseInterface
    {
        $download = $this->adminTransferXlsxExport->getDownloadFile($exportId) ?? $this->adminTransferXlsx->getDownloadFile($exportId);

        if ($download === null):
            throw PageNotFoundException::forPageNotFound();
        endif;

        return $this->response->download($download['path'], null)->setFileName($download['fileName']);
    }

    /** Apre il modale Export condiviso con colonne fisse e senza selezioni per tabella. */
    public function showExportModal(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        if ( ! $this->validateData($this->request->getPost(), ['entity' => 'required|in_list[admintransfer]'])):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/components/export.messages.invalidEntity')]);
        endif;

        return $this->jsonResponse([
            'result' => true,
            'output' => view(Views::EXPORT_MODAL, [
                'entity' => 'admintransfer',
                'controlsPrefix' => 'admintransfer-export',
                'modalTitle' => lang('backend/admintransfer.panels.export'),
                'fixedPackage' => Package::TABLES,
                'packageDescription' => lang('backend/admintransfer.descriptions.export'),
            ]),
        ]);
    }

    /** Annulla e rimuove esclusivamente il job export della sessione corrente. */
    public function removeExport(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        $posts = $this->request->getPost();

        if ( ! $this->validateData($posts, ['exportId' => 'required|regex_match[/^[a-f0-9]{64}$/]'])):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/components/export.messages.invalidEntity')]);
        endif;

        try
        {
            return $this->jsonResponse(['result' => $this->adminTransferXlsxExport->delete($posts['exportId'])]);
        }
        catch (\Throwable $e)
        {
            log_message('error', 'Annullamento export AdminTransfer non riuscito: ' . $e->getMessage());
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/admintransfer.messages.exportError')]);
        }
    }

    /** Scarica un workbook vuoto con le colonne effettive delle quattro tabelle. */
    public function downloadTemplate(): ResponseInterface
    {
        $export = $this->adminTransferXlsx->export(true);

        return $this->download($export['exportId']);
    }

    /** Apre lo stesso modale del componente Import, con gli schemi del pacchetto. */
    public function showImportModal(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        if ( ! $this->validateData($this->request->getPost(), ['entity' => 'required|in_list[admintransfer]'])):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/admintransfer.messages.invalidImportId')]);
        endif;

        try
        {
            $data = [
                'entity' => 'admintransfer',
                'modalTitle' => lang('backend/admintransfer.panels.import'),
                'templateLabel' => lang('backend/admintransfer.buttons.downloadTemplate'),
                'structure' => [],
                'structures' => $this->adminTransferModel->getPackageImportStructures(),
                'downloadUrl' => base_url('backend/admintransfer/downloadTemplate'),
                'uploadName' => 'admintransferFile',
                'uploadAccept' => '.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'uploadLabel' => lang('backend/admintransfer.labels.selectFile'),
                'uploadHelp' => lang('backend/admintransfer.messages.mergeWarning') . ' ' . lang('backend/admintransfer.descriptions.importHelp') . ' ' . lang('backend/admintransfer.descriptions.reExport'),
                'uploadHelpParts' => [['key' => 'backend/admintransfer.messages.mergeWarning'], ' ', ['key' => 'backend/admintransfer.descriptions.importHelp'], ' ', ['key' => 'backend/admintransfer.descriptions.reExport']],
            ];
            $status = null;

            if ($this->request->getGet('resume') === '1'):
                $importId = $this->session->get('admintransferImportId');
                $status = is_string($importId) ? $this->adminTransferXlsxImport->getImportStatus($importId) : null;

                if ($status === null):
                    throw new \DomainException(lang('backend/admintransfer.messages.importNotFound'));
                endif;

                $data['initialOutput'] = $status['status'] === 'staged'
                    ? view(Views::IMPORT_PREVIEW_PARTIAL, $status)
                    : '<div class="alert app-alert-' . ($status['status'] === 'completed' ? 'success' : 'danger') . '">'
                        . esc(lang('backend/admintransfer.messages.' . ($status['status'] === 'completed' ? 'importCompleted' : 'importNeedsReview')))
                        . '<br>' . esc(lang('backend/admintransfer.labels.importId')) . ': ' . esc($status['importId']) . '</div>';
            endif;

            return $this->jsonResponse([
                'result' => true,
                'output' => view(Views::IMPORT_MODAL, $data),
                'loadingOutput' => view(Views::IMPORT_LOADING_PARTIAL),
                'importId' => $status['importId'] ?? null,
                'hasProcessableData' => $status === null || $status['status'] === 'staged',
                'recoveryRequired' => $status !== null && in_array($status['status'], ['processing', 'failed'], true),
            ]);
        }
        catch (\DomainException $e)
        {
            return $this->jsonResponse(['result' => false, 'message' => esc($e->getMessage())]);
        }
        catch (\Throwable $e)
        {
            log_message('error', 'Apertura modale AdminTransfer non riuscita: ' . $e->getMessage());
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/admintransfer.messages.importPrepareError')]);
        }
    }

    /**
     * Carica e valida il pacchetto XLSX senza modificare il database.
     *
     * @return ResponseInterface Risposta JSON con riepilogo e importId
     */
    public function prepareImport(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        $rules = [
            'admintransferFile' => [
                'rules' => 'uploaded[admintransferFile]|ext_in[admintransferFile,xlsx]|max_size[admintransferFile,10240]',
                'errors' => [
                    'uploaded' => lang('backend/admintransfer.messages.fileRequired'),
                    'ext_in' => lang('backend/admintransfer.messages.invalidXlsx'),
                    'max_size' => lang('backend/admintransfer.messages.fileTooLarge'),
                ],
            ],
        ];

        if ( ! $this->validateData($this->request->getPost(), $rules)):
            return $this->jsonResponse([
                'result' => false,
                'message' => implode('<br>', $this->validator->getErrors()),
            ]);
        endif;

        $file = $this->request->getFile('admintransferFile');

        if ($file === null || ! $file->isValid()):
            return $this->jsonResponse([
                'result' => false,
                'message' => lang('backend/admintransfer.messages.fileRequired'),
            ]);
        endif;

        try
        {
            $prepared = $this->adminTransferXlsxImport->prepare($file);

            if ( ! $prepared['result']):
                return $this->jsonResponse([
                    'result' => false,
                    'message' => ui_message('backend/components/import.messages.validationErrors', [count($prepared['validationErrors'])], lang('backend/components/import.messages.validationErrors')),
                    'errorOutput' => view(Views::IMPORT_ERRORS_PARTIAL, ['validationErrors' => $prepared['validationErrors']]),
                ]);
            endif;

            if ($prepared['importId'] !== null):
                $this->session->set('admintransferImportId', $prepared['importId']);
            else:
                $this->session->remove('admintransferImportId');
            endif;

            return $this->jsonResponse([
                'result' => true,
                'importId' => $prepared['importId'],
                'hasProcessableData' => $prepared['plan']['insert'] + $prepared['plan']['update'] > 0,
                'output' => view(Views::IMPORT_PREVIEW_PARTIAL, $prepared),
            ]);
        }
        catch (\DomainException $e)
        {
            return $this->jsonResponse(['result' => false, 'message' => esc($e->getMessage())]);
        }
        catch (\Throwable $e)
        {
            log_message('error', 'Preparazione import AdminTransfer non riuscita: ' . $e->getMessage());

            return $this->jsonResponse([
                'result' => false,
                'message' => lang('backend/admintransfer.messages.importPrepareError'),
            ]);
        }
    }

    /**
     * Conferma ed esegue la fusione atomica del pacchetto AdminTransfer.
     *
     * @return ResponseInterface Risposta JSON con i conteggi importati
     */
    public function executeImport(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        $posts = $this->request->getPost();
        $rules = [
            'importId' => 'required|regex_match[/^[a-f0-9]{64}$/]',
            'step' => 'required|in_list[confirm]',
        ];

        if ( ! $this->validateData($posts, $rules)):
            return $this->jsonResponse([
                'result' => false,
                'message' => implode('<br>', $this->validator->getErrors()),
            ]);
        endif;

        try
        {
            if (($passwordError = $this->requireTransferPassword((string) $posts['importId'])) !== null):
                return $passwordError;
            endif;

            $result = $this->adminTransferXlsxImport->execute((string) $posts['importId']);

            if (($result['result'] ?? true) === false):
                return $this->jsonResponse([
                    'result' => false,
                    'message' => ui_message('backend/components/import.messages.validationErrors', [count($result['validationErrors'])], lang('backend/components/import.messages.validationErrors')),
                    'errorOutput' => view(Views::IMPORT_ERRORS_PARTIAL, ['validationErrors' => $result['validationErrors']]),
                ]);
            endif;

            if ($result['isFinished'] === false):
                return $this->jsonResponse($result + ['progressOutput' => view(Views::IMPORT_LOADING_PARTIAL, ['progressMessage' => $result['progressMessage']])]);
            endif;

            if ( ! $result['alreadyCompleted']):
                /* Il fallimento dell'audit non cambia l'esito di un commit già riuscito. */
                try
                {
                    log_admin_activity(
                        'IMPORT_ADMIN_TRANSFER',
                        'admintransfer',
                        ui_message('backend/admintransfer.audits.import', [$result['inserted'] + $result['updated']], lang('backend/admintransfer.audits.import')),
                        $this->currentAdmin
                    );
                }
                catch (\Throwable $e)
                {
                    log_message('error', 'Audit import AdminTransfer non riuscito: ' . $e->getMessage());
                }
            endif;

            return $this->jsonResponse([
                'result' => true,
                'isFinished' => true,
                'totalInserted' => $result['inserted'],
                'totalUpdated' => $result['updated'],
                'message' => ui_message('backend/admintransfer.messages.importSuccess', [$result['inserted'], $result['updated']], lang('backend/admintransfer.messages.importSuccess')),
                'notificationFailures' => $result['notificationFailures'] ?? 0,
                'notificationWarning' => ($result['notificationFailures'] ?? 0) > 0 ? lang('backend/admintransfer.messages.notificationWarning') : null,
            ]);
        }
        catch (TransferRecoveryException $e)
        {
            return $this->jsonResponse([
                'result' => false,
                'recoveryRequired' => true,
                'importId' => $posts['importId'],
                'message' => lang('backend/admintransfer.messages.importNeedsReview') . ' ' . esc($posts['importId']),
            ]);
        }
        catch (\DomainException $e)
        {
            return $this->jsonResponse(['result' => false, 'message' => esc($e->getMessage())]);
        }
        catch (\Throwable $e)
        {
            log_message('error', 'Import AdminTransfer non riuscito: ' . $e->getMessage());

            return $this->jsonResponse([
                'result' => false,
                'message' => lang('backend/admintransfer.messages.importError'),
            ]);
        }
    }

    /** Elimina soltanto lo staging annullabile appartenente alla sessione corrente. */
    public function deleteImport(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return service('response')->setStatusCode(400);
        endif;

        $posts = $this->request->getPost();

        if ( ! $this->validateData($posts, ['importId' => 'required|regex_match[/^[a-f0-9]{64}$/]'])):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/admintransfer.messages.invalidImportId')]);
        endif;

        try
        {
            if ( ! $this->adminTransferXlsxImport->deleteImport($posts['importId'])):
                throw new \DomainException(lang('backend/admintransfer.messages.importNeedsReview'));
            endif;

            if ($this->session->get('admintransferImportId') === $posts['importId']):
                $this->session->remove('admintransferImportId');
            endif;

            return $this->jsonResponse(['result' => true]);
        }
        catch (\Throwable $e)
        {
            log_message('error', 'Annullamento staging AdminTransfer non riuscito: ' . $e->getMessage());
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/admintransfer.messages.importNotReady')]);
        }
    }
}
