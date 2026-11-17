<?php declare(strict_types = 1);

namespace App\Controllers\Frontend;

use App\Models\ContactMessagesModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Frontend\Contacts;
use Config\Services;

/** Displays the public contact form and accepts validated submissions. */
class ContactsController extends FrontendController
{
    /**
     * Displays the contact form in the public page layout.
     *
     * @return string Rendered contact page.
     * @throws PageNotFoundException If the public contact form is disabled.
     */
    public function index(): string
    {
        if ( ! config(Contacts::class)->enabled):
            throw PageNotFoundException::forPageNotFound();
        endif;

        return $this->render('frontend/pages/pageView', [
            'controller' => 'contacts',
            'action' => 'index',
            'page' => [
                'view' => 'frontend/contacts/formView',
                'title' => 'frontend/contacts.titles.index',
                'description' => 'frontend/contacts.messages.description',
            ],
        ]);
    }

    /**
     * Displays the privacy notice for the public contact form.
     *
     * @return string Rendered contact privacy notice.
     * @throws PageNotFoundException If the public contact form is disabled.
     */
    public function privacy(): string
    {
        if ( ! config(Contacts::class)->enabled):
            throw PageNotFoundException::forPageNotFound();
        endif;

        return $this->render('frontend/pages/pageView', [
            'controller' => 'contacts',
            'action' => 'privacy',
            'page' => [
                'view' => 'frontend/contacts/privacyView',
                'title' => 'frontend/contacts.privacy.title',
                'description' => 'frontend/contacts.privacy.description',
            ],
        ]);
    }

    /**
     * Checks the submission, stores it, and optionally notifies the configured recipient.
     *
     * @return ResponseInterface JSON result with the current CSRF token.
     * @throws PageNotFoundException If the public contact form is disabled.
     */
    public function send(): ResponseInterface
    {
        $config = config(Contacts::class);
        if ( ! $config->enabled):
            throw PageNotFoundException::forPageNotFound();
        endif;
        if ( ! $this->request->isAJAX()):
            return $this->response->setStatusCode(400);
        endif;

        $attemptKey = 'contacts-' . hash('sha256', $this->request->getIPAddress());
        if ( ! service('throttler')->check($attemptKey, 5, 600)):
            return $this->jsonResponse(['result' => false, 'message' => lang('frontend/contacts.messages.tooManyAttempts')]);
        endif;

        $website = $this->request->getPost('website');
        if ($website !== null && $website !== ''):
            return $this->jsonResponse(['result' => false, 'message' => lang('frontend/contacts.messages.failed')]);
        endif;

        $rules = [
            'name' => ['label' => lang('frontend/contacts.fields.name'), 'rules' => 'required|max_length[120]'],
            'email' => ['label' => lang('frontend/contacts.fields.email'), 'rules' => 'required|valid_email|max_length[254]'],
            'phone' => ['label' => lang('frontend/contacts.fields.phone'), 'rules' => 'required|regex_match[/^[0-9+().\-\s]{6,30}$/D]'],
            'message' => ['label' => lang('frontend/contacts.fields.message'), 'rules' => 'required|max_length[3000]'],
            'privacy' => ['label' => lang('frontend/contacts.privacy.title'), 'rules' => 'required|in_list[1]', 'errors' => ['required' => lang('frontend/contacts.privacy.required'), 'in_list' => lang('frontend/contacts.privacy.required')]],
        ];
        $data = [];
        foreach (array_keys($rules) as $field):
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : '';
        endforeach;
        if ( ! $this->validateData($data, $rules)):
            return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('frontend/contacts.messages.validationErrors')]);
        endif;

        unset($data['privacy']);
        try {
            $id = model(ContactMessagesModel::class)->saveMessage($data);
        } catch (\Throwable) {
            $id = 0;
        }
        if ($id === 0):
            log_message('error', 'Unable to store a contact message.');
            return $this->jsonResponse(['result' => false, 'message' => lang('frontend/contacts.messages.failed')]);
        endif;

        if ($config->notificationRecipient !== ''):
            $this->sendNotification($id, $config->notificationRecipient);
        endif;

        return $this->jsonResponse(['result' => true, 'message' => lang('frontend/contacts.messages.received')]);
    }

    /**
     * Sends a notification containing only a link to the stored message.
     *
     * @param int $id ID of the stored message.
     * @param string $recipient Configured notification address.
     * @return void
     */
    private function sendNotification(int $id, string $recipient): void
    {
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false):
            log_message('error', 'The contact notification recipient is invalid.');
            return;
        endif;

        try {
            $settings = setting('Backend\Email');
            $email = Services::email(null, false);
            $email->setFrom($settings->fromEmail, $settings->fromName);
            $email->setTo($recipient);
            $email->setMailType('text');
            $email->setSubject(lang('backend/messages.notifications.subject', [], $this->notificationLocale()));
            $email->setMessage(site_url('backend/messages/show/' . $id));
            if ($email->send()):
                return;
            endif;
        } catch (\Throwable) {
            /* The saved message remains available when the mail service fails. */
        }

        log_message('error', 'Unable to send the contact notification for message {id}.', ['id' => $id]);
    }

    /**
     * Determines the notification language from the backend settings.
     *
     * @return string Supported backend language, with English as the fallback.
     */
    private function notificationLocale(): string
    {
        $locale = setting('Backend\General', 'language');

        return is_string($locale) && in_array($locale, config('App')->supportedLocales, true) ? $locale : 'en';
    }
}
