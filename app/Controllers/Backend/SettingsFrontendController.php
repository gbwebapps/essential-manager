<?php declare(strict_types = 1);

namespace App\Controllers\Backend;

use App\Libraries\Backend\SettingsFrontendClass;
use App\Models\Backend\SettingsFrontendModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Espone ai soli superadmin la configurazione del sito pubblico con le protezioni dei Settings esistenti.
 */
class SettingsFrontendController extends BackendController
{
    protected SettingsFrontendModel $settingsFrontendModel;
    protected SettingsFrontendClass $settingsFrontendClass;

    /**
     * Prepara i componenti dedicati senza usare il model o il JavaScript dei Settings Backend.
     *
     * @param RequestInterface $request Richiesta HTTP corrente.
     * @param ResponseInterface $response Risposta HTTP corrente.
     * @param LoggerInterface $logger Logger applicativo.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->settingsFrontendModel = model(SettingsFrontendModel::class);
        $this->settingsFrontendClass = new SettingsFrontendClass();
        $this->data['controller'] = 'settingsFrontend';
    }

    /**
     * Mostra l'index amministrativa dedicata alla configurazione del frontend.
     *
     * @return string
     */
    public function index(): string
    {
        $this->data['action'] = 'index';
        $this->data['title'] = lang('backend/settingsFrontend.titles.index');
        $this->data['icon'] = '<i class="fa-solid fa-sliders"></i>';
        return $this->render('backend/settingsFrontend/indexView', $this->data);
    }

    /**
     * Rilegge il form senza scrivere impostazioni o cambiare la lingua dell'amministratore.
     *
     * @return ResponseInterface
     */
    public function openSettings(): ResponseInterface
    {
        if ( ! $this->isAjaxPost()):
            return $this->response->setStatusCode(400);
        endif;
        $this->prepareViewData();
        return $this->jsonResponse(['result' => true, 'output' => view('backend/settingsFrontend/partials/index/generalSettingsPartial', $this->data)]);
    }

    /**
     * Valida il form completo e salva soltanto il namespace Frontend\General dopo la conferma password.
     *
     * @return ResponseInterface
     */
    public function saveSettings(): ResponseInterface
    {
        if ( ! $this->isAjaxPost()):
            return $this->response->setStatusCode(400);
        endif;
        $posts = $this->settingsFrontendModel->prepareGeneralPosts($this->request->getPost());
        if ($posts === null):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/settingsFrontend.messages.validationErrors')]);
        endif;
        if ( ! $this->validateData($posts, $this->settingsFrontendModel->generalSettingsValidateRules())):
            return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('backend/settingsFrontend.messages.validationErrors')]);
        endif;

        if (($passwordError = $this->requireCurrentPassword()) !== null):
            return $passwordError;
        endif;

        $result = $this->settingsFrontendModel->saveSettings('Frontend\General', $posts);
        if (($result['result'] ?? false) !== true):
            return $this->jsonResponse($result ?? ['result' => false, 'message' => lang('backend/settingsFrontend.messages.saveError')]);
        endif;
        $this->prepareViewData();
        return $this->jsonResponse($result + ['output' => view('backend/settingsFrontend/partials/index/generalSettingsPartial', $this->data)]);
    }

    /**
     * Verifica la presenza di personalizzazioni senza richiedere password per una semplice lettura.
     *
     * @return ResponseInterface
     */
    public function checkDeleteSettings(): ResponseInterface
    {
        if ( ! $this->isAjaxPost()):
            return $this->response->setStatusCode(400);
        endif;
        $exists = $this->settingsFrontendModel->hasDatabaseSettings('Frontend\General');
        return $this->jsonResponse(['result' => $exists, 'message' => $exists ? '' : lang('backend/settingsFrontend.messages.alreadyDefault')]);
    }

    /**
     * Rimuove le personalizzazioni frontend e torna ai file senza interessare le impostazioni backend.
     *
     * @return ResponseInterface
     */
    public function deleteSettings(): ResponseInterface
    {
        if ( ! $this->isAjaxPost()):
            return $this->response->setStatusCode(400);
        endif;
        if (($passwordError = $this->requireCurrentPassword()) !== null):
            return $passwordError;
        endif;
        $deleted = $this->settingsFrontendModel->deleteSettings('Frontend\General');
        if ($deleted !== true):
            return $this->jsonResponse(['result' => false, 'message' => lang($deleted === null ? 'backend/settingsFrontend.messages.alreadyDefault' : 'backend/settingsFrontend.messages.deleteError')]);
        endif;
        return $this->jsonResponse(['result' => true, 'message' => lang('backend/settingsFrontend.messages.deleteSuccess')]);
    }

    /**
     * Prepara valori grezzi e opzioni globali per il form.
     *
     * @return void
     */
    private function prepareViewData(): void
    {
        $this->data['frontendSettings'] = $this->settingsFrontendModel->getSettings('Frontend\General');
        $this->data['isFromDatabase'] = $this->settingsFrontendModel->hasDatabaseSettings('Frontend\General');
        $this->data['languages'] = $this->settingsFrontendClass->getLanguages();
        $this->data['timezones'] = $this->settingsFrontendClass->getTimezones();
    }

    /**
     * Restringe tutte le operazioni asincrone a POST con intestazione AJAX.
     *
     * @return bool
     */
    private function isAjaxPost(): bool
    {
        return $this->request->isAJAX() && $this->request->is('post');
    }
}
