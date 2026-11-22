<?php declare(strict_types=1);

namespace App\Controllers\Backend\Components;

use App\Controllers\Backend\BackendController;
use App\Libraries\Backend\AuthorizationClass;
use App\Models\Backend\Components\GalleryOneModel;
use App\Models\Backend\UsersModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Gestisce le operazioni asincrone del componente galleria immagini singola (Gallery One), inclusa la visualizzazione, l'eliminazione e la gestione delle copertine.
 */
class GalleryOneController extends BackendController
{
    private GalleryOneModel $galleryModel;

    public function __construct()
    {
        $this->galleryModel = model(GalleryOneModel::class);
    }

    private function getPosts(?string $scope = null): array
    {
        $posts = $this->request->getPost();

        if ($scope === 'account'):
            $posts['entity'] = 'admins';
            $posts['uuid'] = (string) ($this->currentAdmin->uuid ?? '');
        endif;

        return $posts;
    }

    private function canAccess(array $posts, bool $readOnly = false): bool
    {
        $entity = (string) ($posts['entity'] ?? '');
        if ( ! config(\Config\Backend\Features::class)->isEntityEnabled($entity)):
            return false;
        endif;
        if ($entity === 'users'):
            if ( ! $readOnly || $posts['context'] !== 'show'
                || ! AuthorizationClass::allows($this->currentAdmin, 'users_show')):
                return false;
            endif;
            $record = model(UsersModel::class)->getByUUID($posts['uuid']);
            return $record['result'] === true && $record['row']->deleted_at === null;
        endif;

        $galleryPermissions = config(\Config\Backend\Permissions::class)->galleryPermissions;

        if (array_key_exists($entity, $galleryPermissions)):
            if ($this->currentAdmin === null):
                return false;
            endif;

            if (isset($this->currentAdmin->permissions->all) && $this->currentAdmin->permissions->all === true):
                return true;
            endif;

            foreach ($galleryPermissions[$entity] as $permission):
                if (isset($this->currentAdmin->permissions->{$permission}) && $this->currentAdmin->permissions->{$permission} === true):
                    return true;
                endif;
            endforeach;

            return false;
        endif;

        if ($entity !== 'admins'):
            return true;
        endif;

        if ($this->currentAdmin === null):
            return false;
        endif;

        return (int) $this->currentAdmin->superadmin === 1
            || hash_equals((string) $this->currentAdmin->uuid, (string) ($posts['uuid'] ?? ''));
    }

    /** Adds an avatar update only for the gallery of the signed-in administrator. */
    private function adminAvatarUpdate(array $posts): array
    {
        if ($posts['entity'] === 'admins' && $posts['uuid'] === ($this->currentAdmin->uuid ?? null)):
            return ['avatarUrl' => $this->getAdminAvatarUrl()];
        endif;

        return [];
    }

    private function accessDenied(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/global.errors.err403')], 403);
    }

    private function invalidRequest(): ResponseInterface
    {
        return $this->jsonResponse(['result' => false, 'message' => lang('backend/global.errors.err403')], 400);
    }

    public function showGallery(?string $scope = null): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts($scope);
            $rules = $this->galleryModel->getImagesValidateFields();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result' => false, 'message' => ui_message('backend/components/galleryOne.messages.validationToastErrors', [$errorMessage], lang('backend/components/galleryOne.messages.validationToastErrors'))]);
            endif;

            if ( ! $this->canAccess($posts, readOnly: true)):
                return $this->accessDenied();
            endif;

            $data = ['entity' => $posts['entity'], 'uuid' => $posts['uuid'], 'context' => $posts['context'], 'images' => $this->galleryModel->getImages($posts)];
            return $this->jsonResponse(['result' => true, 'output' => view('backend/components/galleryOne/galleryOneView', $data)] + $this->adminAvatarUpdate($posts));
        endif;

        return $this->invalidRequest();
    }

    public function deleteImage(?string $scope = null): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts($scope);
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

            $data = ['entity' => $posts['entity'], 'uuid' => $posts['uuid'], 'context' => $posts['context'], 'filename' => $posts['filename'], 'images' => $this->galleryModel->getImages($posts)];
            return $this->jsonResponse(['result' => true, 'message' => lang('backend/components/galleryOne.messages.deleteSuccess'), 'output' => view('backend/components/galleryOne/galleryOneView', $data)] + $this->adminAvatarUpdate($posts));
        endif;

        return $this->invalidRequest();
    }

    public function setCover(?string $scope = null): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts($scope);
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

            $data = ['entity' => $posts['entity'], 'uuid' => $posts['uuid'], 'context' => $posts['context'], 'images' => $this->galleryModel->getImages($posts)];
            return $this->jsonResponse(['result' => true, 'message' => lang('backend/components/galleryOne.messages.setCoverSuccess'), 'output' => view('backend/components/galleryOne/galleryOneView', $data)] + $this->adminAvatarUpdate($posts));
        endif;

        return $this->invalidRequest();
    }

    public function removeCover(?string $scope = null): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->getPosts($scope);
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

            $data = ['entity' => $posts['entity'], 'uuid' => $posts['uuid'], 'context' => $posts['context'], 'images' => $this->galleryModel->getImages($posts)];
            return $this->jsonResponse(['result' => true, 'message' => lang('backend/components/galleryOne.messages.removeCoverSuccess'), 'output' => view('backend/components/galleryOne/galleryOneView', $data)] + $this->adminAvatarUpdate($posts));
        endif;

        return $this->invalidRequest();
    }
}
