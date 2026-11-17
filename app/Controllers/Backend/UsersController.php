<?php declare(strict_types = 1);

namespace App\Controllers\Backend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\UserAgent;
use Psr\Log\LoggerInterface;
use App\Models\Backend\UsersModel;
use App\Libraries\Backend\AuthorizationClass;
use App\Libraries\Backend\UsersClass;
use App\Models\Backend\Components\GalleryOneModel;

/**
 * Provides user records and actions to authorized administrators.
 */
class UsersController extends BackendController
{
    protected UsersModel $usersModel;
    protected UsersClass $usersClass;
    protected GalleryOneModel $galleryOneModel;

    /**
     * Inizializza il controller e prepara i dati della sezione.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request Richiesta HTTP corrente.
     * @param \CodeIgniter\HTTP\ResponseInterface $response Risposta HTTP corrente.
     * @param \Psr\Log\LoggerInterface $logger Servizio di registrazione degli errori.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->data['controller'] = 'users';
        $this->data['entity'] = 'users';
        $this->usersModel = model(UsersModel::class);
        $this->usersClass = new UsersClass();
        $this->galleryOneModel = model(GalleryOneModel::class);
    }

    /**
     * L'anagrafica viene modificata dal cliente; qui si accede soltanto al dettaglio.
     *
     * @param string $uuid UUID del cliente o del proprietario delle immagini.
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function show(string $uuid): RedirectResponse|string
    {
        $returnUrl = base_url(AuthorizationClass::allows($this->currentAdmin, 'users_show_all') ? 'backend/users/showAll' : 'backend/dashboard');
        if ( ! $this->regexp->validateUUID($uuid)):
            return redirect()->to($returnUrl)->with('message', lang('backend/users.errors.uuid'));
        endif;
        $record = $this->usersModel->getByUUID($uuid);
        if ($record['result'] === false || $record['row']->deleted_at !== null):
            return redirect()->to($returnUrl)->with('message', $record['message'] ?? lang('backend/users.messages.cannotModifyDeleted'));
        endif;
        $this->data['action'] = 'show';
        $this->data['title'] = lang('backend/users.titles.show');
        $this->data['icon'] = '<i class="fa-solid fa-user"></i>';
        $this->data['admin'] = $record['row'];
        $this->data['uuid'] = $uuid;
        $this->data['context'] = 'show';
        $this->data['images'] = $this->galleryOneModel->getImages(['entity' => 'users', 'uuid' => $uuid]);
        $this->data['userAgent'] = new UserAgent();
        $this->data['tokens'] = $this->usersModel->getTokens($uuid);
        return $this->render('backend/users/showView', $this->data);
    }

    /**
     * Restituisce i dati anagrafici dell'utente per il dettaglio amministrativo.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getGeneralData(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return $this->response->setStatusCode(400);
        endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->usersModel->delValidationRules())):
            return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors()]);
        endif;
        $record = $this->usersModel->getByUUID($posts['uuid']);
        if (($error = $this->targetAccessError($record)) !== null):
            return $error;
        endif;
        $this->data['admin'] = $record['row'];
        return $this->jsonResponse(['result' => true, 'output' => view('backend/users/partials/show/generalDataPartial', $this->data)]);
    }


	/**
	 * Blocca l'accesso a un utente inesistente o non disponibile.
	 *
	 * @param array $record Risultato del recupero dell'utente dal model.
	 * @return \CodeIgniter\HTTP\ResponseInterface|null
	 */
	private function targetAccessError(array $record): ?ResponseInterface
	{
		if ($record['result'] === false):
			return $this->jsonResponse(['result' => false, 'message' => $record['message']]);
		endif;

		if (($record['row']->deleted_at ?? null) !== null):
			return $this->jsonResponse(['result' => false, 'message' => lang('backend/users.messages.cannotModifyDeleted')]);
		endif;

		return null;
	}

    /**
     * Gestisce la pagina e gli aggiornamenti filtrati della lista utenti.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function showAll(): string|ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
        
            $rules = $this->usersModel->showAllValidationRules();
            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result'  => false, 'message' => ui_message('backend/users.messages.validationToastErrors', [$errorMessage], lang('backend/users.messages.validationToastErrors'))]);
            endif;

            $posts['searchFields'] = $posts['searchFields'] ?? [];
            $posts['searchDates']  = $posts['searchDates']  ?? [];

            $rules = $this->usersModel->showAllSearchValidationRules();
            if ( ! $this->validateData($posts, $rules)):
                $formattedErrors = removeDot('searchFields.', $this->validator->getErrors());
                $formattedErrors = removeDot('searchDates.', $formattedErrors);
                return $this->jsonResponse(['errors' => $formattedErrors, 'message' => lang('backend/users.messages.validationErrors')]);
            endif;
            
            $this->data['data'] = $this->usersModel->getData($posts);

            $json = [];

            if($this->data['data']['result'] === true):

                $this->data['posts'] = $posts;
                $this->data['removalNow'] = date('Y-m-d H:i:s');

                $json['output'] = view('backend/users/partials/showAll/showAllPartial', $this->data);
                $json['trashCounts'] = $this->usersModel->getTrashCounts();
                $json['menuCounts'] = model(\App\Models\Backend\MenuCountsModel::class)->getCounts($this->currentAdmin, 'users');
                $json['result'] = true;

            elseif($this->data['data']['result'] === false):

                $json['result'] = false;
                $json['message'] = $this->data['data']['message'];

            endif;

            return $this->jsonResponse($json);

        endif;

        $this->data['action'] = 'showAll';
        
        $this->data['title'] = lang('backend/users.titles.showAll');
        $this->data['icon'] = '<i class="fa-solid fa-users"></i>';

        return $this->render('backend/users/showAllView', $this->data);
    }

    /**
     * Avvia il recupero credenziali e restituisce il risultato dell'operazione.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function resetPassword(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->usersModel->resetPasswordValidationRules();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                
                return $this->jsonResponse(['result'  => false, 'message' => ui_message('backend/users.messages.validationToastErrors', [$errorMessage], lang('backend/users.messages.validationToastErrors'))]);
            endif;

            $json = $this->usersModel->resetPassword($posts, $this->request);

			return $this->jsonResponse($json);

		endif;

		return service('response')->setStatusCode(400);
	}

    /**
     * Sospende o riattiva un cliente dopo la conferma dell'amministratore.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function changeStatus(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->usersModel->changeStatusValidationRules();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                
                return $this->jsonResponse(['result'  => false, 'message' => ui_message('backend/users.messages.validationToastErrors', [$errorMessage], lang('backend/users.messages.validationToastErrors'))]);
            endif;

            if (($passwordError = $this->requireCurrentPassword()) !== null):
                return $passwordError;
            endif;

            $json = $this->usersModel->changeStatus($posts);

            if($json['result'] === false):
                return $this->jsonResponse(['result' => false, 'message' => $json['message']]);
            endif;

            if (($posts['context'] ?? '') === 'show' && AuthorizationClass::allows($this->currentAdmin, 'users_show')):

                $this->data['admin'] = $json['admin'];

                $json['statusView'] = view('backend/users/partials/show/changeStatusPartial', $this->data);
                $json['metaView'] = view('backend/users/partials/common/metaDataPartial', $this->data); 

            endif;

            unset($json['admin']);

			return $this->jsonResponse($json);

		endif;

		return service('response')->setStatusCode(400);
	}

    /**
     * Restituisce i metadati del cliente selezionato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getMetaData(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->usersModel->metaDataValidationRules();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                
                return $this->jsonResponse(['result'  => false, 'message' => ui_message('backend/users.messages.validationToastErrors', [$errorMessage], lang('backend/users.messages.validationToastErrors'))]);
            endif;

			$record = $this->usersModel->getByUUID($posts['uuid']);

			if (($accessError = $this->targetAccessError($record)) !== null):
				return $accessError;
			endif;

			if($record['result'] === true):

                $json = ['result' => true];

                $this->data['admin'] = $record['row'];

                $json['output'] = view('backend/users/partials/common/metaDataPartial', $this->data);

            else:

                $json = ['result' => false];
                $json['message'] = $record['message'];

			endif;

			return $this->jsonResponse($json);

		endif;

		return service('response')->setStatusCode(400);
    }

    /**
     * Restituisce i token del cliente selezionato nel dettaglio amministrativo.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getTokens(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->usersModel->getTokensValidationRules();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                
                return $this->jsonResponse(['result'  => false, 'message' => ui_message('backend/users.messages.validationToastErrors', [$errorMessage], lang('backend/users.messages.validationToastErrors'))]);
            endif;

			$record = $this->usersModel->getByUUID($posts['uuid']);

			if (($accessError = $this->targetAccessError($record)) !== null):
				return $accessError;
			endif;

			if($record['result'] === true):

                $json = ['result' => true];

                $this->data['admin'] = $record['row']; 

                $this->data['userAgent'] = new UserAgent();
                $this->data['tokens'] = $this->usersModel->getTokens($posts['uuid']);

                $json['output'] = view('backend/users/partials/show/tokensPartial', $this->data);

            else:

                $json = ['result' => false];
                $json['message'] = $record['message'];

			endif;

			return $this->jsonResponse($json);

		endif;

		return service('response')->setStatusCode(400);
    }

    /**
     * Valida e revoca un token dell'utente, aggiornandone l'elenco.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function deleteToken(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->usersModel->deleteTokenValidationRules();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                
                return $this->jsonResponse(['result'  => false, 'message' => ui_message('backend/users.messages.validationToastErrors', [$errorMessage], lang('backend/users.messages.validationToastErrors'))]);
            endif;

            if (($passwordError = $this->requireCurrentPassword()) !== null):
                return $passwordError;
            endif;

            $result = $this->usersModel->deleteToken($posts);

            $json = ['result' => $result['result'], 'message' => $result['message']];
            if ($result['result'] === true && AuthorizationClass::allows($this->currentAdmin, 'users_show')):
                $this->data['admin'] = $result['admin'];
                $this->data['userAgent'] = new UserAgent();
                $this->data['tokens'] = $this->usersModel->getTokens($posts['uuid']);
                $json['tokensView'] = view('backend/users/partials/show/tokensPartial', $this->data);
            endif;

			return $this->jsonResponse($json);

		endif;

		return service('response')->setStatusCode(400);
	}

    /**
     * Ripristina un account Removed entro la scadenza, dopo la conferma obbligatoria dello staff.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function restoreDelete(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return $this->response->setStatusCode(400);
        endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->usersModel->delValidationRules())):
            return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('backend/users.messages.validationErrors')]);
        endif;
        if (($passwordError = $this->requireMandatoryCurrentPassword()) !== null):
            return $passwordError;
        endif;
        return $this->jsonResponse($this->usersModel->restoreDelete($posts));
    }

    /** Permanently deletes a user whose account-removal deadline has passed. */
    public function hardDelete(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return $this->response->setStatusCode(400);
        endif;
        $posts = $this->request->getPost();
        if ( ! $this->validateData($posts, $this->usersModel->delValidationRules())):
            return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('backend/users.messages.validationErrors')]);
        endif;
        if (($passwordError = $this->requireMandatoryCurrentPassword()) !== null):
            return $passwordError;
        endif;
        return $this->jsonResponse($this->usersModel->hardDelete($posts));
    }
}
