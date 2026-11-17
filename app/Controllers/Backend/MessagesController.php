<?php declare(strict_types = 1);

namespace App\Controllers\Backend;

use App\Libraries\Backend\AuthorizationClass;
use App\Libraries\Backend\MessagesClass;
use App\Models\ContactMessagesModel;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Lists contact messages and changes their status for authorized administrators. */
class MessagesController extends BackendController
{
    private const SORT_COLUMNS = ['id', 'name', 'email', 'status', 'created_at'];

    protected MessagesClass $messagesClass;

    /**
     * Initializes the presentation class for the standard backend assets.
     *
     * @param RequestInterface $request Current HTTP request.
     * @param ResponseInterface $response Current HTTP response.
     * @param LoggerInterface $logger Application logger.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->data['controller'] = 'messages';
        $this->messagesClass = new MessagesClass();
    }

    /**
     * Displays the administrative inbox with optional status filtering.
     *
     * @return string|ResponseInterface Rendered inbox or an AJAX list response.
     */
    public function showAll(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            if ( ! $this->request->isAJAX()):
                return $this->response->setStatusCode(400);
            endif;

            $posts = $this->request->getPost();
            $rules = [
                'column' => 'required|in_list[' . implode(',', self::SORT_COLUMNS) . ']',
                'order' => 'required|in_list[asc,desc]',
                'page' => 'required|is_natural_no_zero',
                'rows' => 'required|in_list[5,10,15,20]',
                'trash_filter' => 'required|in_list[all,' . implode(',', ContactMessagesModel::STATUSES) . ']',
            ];
            if ( ! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/messages.messages.invalidFilters')]);
            endif;

            $posts['searchFields'] = $posts['searchFields'] ?? [];
            $posts['searchDates'] = $posts['searchDates'] ?? [];
            if ( ! is_array($posts['searchFields']) || ! is_array($posts['searchDates'])):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/messages.messages.invalidFilters')]);
            endif;

            $model = model(ContactMessagesModel::class);
            if ( ! $this->validateData($posts, $model->showAllSearchValidationRules())):
                $errors = removeDot('searchFields.', $this->validator->getErrors());
                $errors = removeDot('searchDates.', $errors);

                return $this->jsonResponse(['result' => false, 'errors' => $errors, 'message' => lang('backend/messages.messages.invalidFilters')]);
            endif;
            $data = $model->getMessages(
                $posts['trash_filter'] === 'all' ? null : $posts['trash_filter'],
                (int) $posts['page'],
                (int) $posts['rows'],
                $posts['searchFields'],
                $posts['searchDates'],
                $posts['column'],
                $posts['order']
            );

            $counts = $model->getStatusCounts();

            return $this->jsonResponse([
                'result' => true,
                'output' => view('backend/messages/partials/showAll/showAllPartial', ['data' => $data, 'posts' => $posts, 'currentAdmin' => $this->currentAdmin]),
                'trashCounts' => $counts + ['all' => array_sum($counts)],
                'menuCounts' => ['messages' => $counts],
            ]);
        endif;

        $this->data['action'] = 'showAll';
        $this->data['title'] = lang('backend/messages.titles.showAll');
        $this->data['icon'] = '<i class="fa-solid fa-envelope"></i>';

        return $this->render('backend/messages/showAllView');
    }

    /**
     * Displays a contact message without changing its status.
     *
     * @param string $id Message ID from the route.
     * @return string Rendered message detail.
     * @throws PageNotFoundException If the message does not exist.
     */
    public function show(string $id): string
    {
        $message = model(ContactMessagesModel::class)->getMessage($id);
        if ($message === null):
            throw PageNotFoundException::forPageNotFound();
        endif;

        $this->data['action'] = 'show';
        $this->data['title'] = lang('backend/messages.titles.show');
        $this->data['icon'] = '<i class="fa-solid fa-envelope-open"></i>';

        if (AuthorizationClass::allows($this->currentAdmin, 'messages_show_all')):
            $this->data['linksBar'] = [[
                'icon' => '<i class="fa-solid fa-envelope"></i>',
                'label' => lang('backend/messages.actions.back'),
                'route' => 'backend/messages/showAll',
            ]];
        endif;

        return $this->render('backend/messages/showView', ['contactMessage' => $message]);
    }

    /**
     * Reloads the detail panel using the standard backend data manager.
     *
     * @return ResponseInterface JSON result with the current message panel.
     */
    public function getGeneralData(): ResponseInterface
    {
        if ( ! $this->request->isAJAX()):
            return $this->response->setStatusCode(400);
        endif;

        $id = $this->request->getPost('id');
        if ( ! is_string($id) || ! ctype_digit($id)):
            return $this->response->setStatusCode(400);
        endif;

        $message = model(ContactMessagesModel::class)->getMessage($id);
        if ($message === null):
            return $this->response->setStatusCode(404);
        endif;

        return $this->jsonResponse([
            'result' => true,
            'output' => view('backend/messages/partials/show/generalDataPartial', ['contactMessage' => $message, 'currentAdmin' => $this->currentAdmin]),
        ]);
    }

    /**
     * Changes the status after validating the request and checking the administrator password policy.
     *
     * @param string $id Message ID from the route.
     * @return ResponseInterface JSON result with the current CSRF token.
     */
    public function changeStatus(string $id): ResponseInterface
    {
        if ( ! $this->request->isAJAX()):
            return $this->response->setStatusCode(400);
        endif;

        $previous = $this->request->getPost('previousStatus');
        $status = $this->request->getPost('status');
        if ( ! in_array($previous, ContactMessagesModel::STATUSES, true)
            || ! in_array($status, ContactMessagesModel::STATUSES, true)
            || $previous === $status):
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/messages.messages.invalidStatus')]);
        endif;

        if (($passwordError = $this->requireCurrentPassword()) !== null):
            return $passwordError;
        endif;

        try {
            $saved = model(ContactMessagesModel::class)->changeStatus($id, $previous, $status);
        } catch (\Throwable) {
            log_message('error', 'Unable to change the status of contact message {id}.', ['id' => $id]);
            return $this->jsonResponse(['result' => false, 'message' => lang('backend/messages.messages.saveFailed')]);
        }

        return $this->jsonResponse([
            'result' => $saved,
            'status' => $saved ? $status : null,
            'menuCounts' => $saved ? model(\App\Models\Backend\MenuCountsModel::class)->getCounts($this->currentAdmin, 'messages') : [],
            'message' => lang('backend/messages.messages.' . ($saved ? 'statusSaved' : 'statusConflict')),
        ]);
    }
}
