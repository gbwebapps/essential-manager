<?php declare(strict_types=1);

namespace App\Controllers\Frontend\Components;

use App\Controllers\Frontend\FrontendController;
use App\Models\Frontend\Components\GalleryOneModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Gestisce le operazioni asincrone del componente galleria immagini singola (Gallery One), inclusa la visualizzazione, l'eliminazione e la gestione delle copertine.
 */
class GalleryOneController extends FrontendController
{
    private GalleryOneModel $galleryModel;

    /**
     * Inizializza le dipendenze necessarie al componente.
     *
     * @return void
     */
    public function __construct()
    {
        $this->galleryModel = model(GalleryOneModel::class);
    }

    /**
     * Prepara il payload immagini vincolandolo all'utente autenticato.
     *
     * @return array
     */
    private function getPosts(): array
    {
        $posts = $this->request->getPost();
        $posts['entity'] = 'users';
        $posts['context'] = 'edit';
        $posts['uuid'] = (string) ($this->currentUser->uuid ?? '');
        return $posts;
    }

    /**
     * L'accesso alle immagini resta vincolato all'utente autenticato.
     *
     * @param array $posts Payload dell'operazione sottoposto alla whitelist.
     * @return bool
     */
    private function canAccess(array $posts): bool
    {
        return $this->currentUser !== null && ($posts['entity'] ?? '') === 'users'
            && hash_equals((string) $this->currentUser->uuid, (string) ($posts['uuid'] ?? ''));
    }

    /**
     * Restituisce il rifiuto JSON per un accesso non autorizzato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    private function accessDenied(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/global.errors.err403')], 403);
    }

    /**
     * Restituisce l'errore JSON per una richiesta non valida.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    private function invalidRequest(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/global.errors.err403')], 400);
    }

    /**
     * Restituisce la galleria aggiornata e la copertina da mostrare nel menu.
     *
     * @param array $posts Parametri già vincolati all'utente autenticato.
     * @param string|null $message Messaggio di conferma per una modifica alla galleria.
     * @return ResponseInterface
     */
    private function galleryResponse(array $posts, ?string $message = null): ResponseInterface
    {
        $data = [
            'entity' => $posts['entity'],
            'uuid' => $posts['uuid'],
            'context' => $posts['context'],
            'images' => $this->galleryModel->getImages($posts),
        ];

        return $this->jsonResponse([
            'result' => true,
            'message' => $message,
            'output' => view('backend/components/galleryOne/galleryOneView', $data),
            'avatarUrl' => $this->galleryModel->getCoverUrl($posts['uuid']),
        ]);
    }

    /**
     * Valida la richiesta e restituisce la galleria dell'utente autenticato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function showGallery(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts();
            $rules = $this->galleryModel->getImagesValidateFields();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result' => false, 'message' => ui_message('backend/components/galleryOne.messages.validationToastErrors', [$errorMessage], lang('backend/components/galleryOne.messages.validationToastErrors'))]);
            endif;

            if ( ! $this->canAccess($posts)):
                return $this->accessDenied();
            endif;

            return $this->galleryResponse($posts);
        endif;

        return $this->invalidRequest();
    }

    /**
     * Elimina un'immagine del cliente autenticato e aggiorna la galleria.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function deleteImage(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts();
            $rules = $this->galleryModel->deleteImageValidateFields();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result' => false, 'message' => ui_message('backend/components/galleryOne.messages.validationToastErrors', [$errorMessage], lang('backend/components/galleryOne.messages.validationToastErrors'))]);
            endif;

            if ( ! $this->canAccess($posts)):
                return $this->accessDenied();
            endif;

            if ( ! $this->galleryModel->deleteImage($posts)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/components/galleryOne.messages.deleteError')]);
            endif;

            return $this->galleryResponse($posts, lang('backend/components/galleryOne.messages.deleteSuccess'));
        endif;

        return $this->invalidRequest();
    }

    /**
     * Imposta la copertina tra le immagini dell'utente autenticato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function setCover(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts();
            $rules = $this->galleryModel->coverValidateFields();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result' => false, 'message' => ui_message('backend/components/galleryOne.messages.validationToastErrors', [$errorMessage], lang('backend/components/galleryOne.messages.validationToastErrors'))]);
            endif;

            if ( ! $this->canAccess($posts)):
                return $this->accessDenied();
            endif;

            if ( ! $this->galleryModel->setCover($posts)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/components/galleryOne.messages.setCoverError')]);
            endif;

            return $this->galleryResponse($posts, lang('backend/components/galleryOne.messages.setCoverSuccess'));
        endif;

        return $this->invalidRequest();
    }

    /**
     * Rimuove la copertina dalle immagini dell'utente autenticato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function removeCover(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts();
            $rules = $this->galleryModel->coverValidateFields();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result' => false, 'message' => ui_message('backend/components/galleryOne.messages.validationToastErrors', [$errorMessage], lang('backend/components/galleryOne.messages.validationToastErrors'))]);
            endif;

            if ( ! $this->canAccess($posts)):
                return $this->accessDenied();
            endif;

            if ( ! $this->galleryModel->removeCover($posts)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/components/galleryOne.messages.removeCoverError')]);
            endif;

            return $this->galleryResponse($posts, lang('backend/components/galleryOne.messages.removeCoverSuccess'));
        endif;

        return $this->invalidRequest();
    }
}
