<?php declare(strict_types = 1);

namespace App\Libraries\Backend;

use App\Models\Backend\AccountModel;
use CodeIgniter\HTTP\IncomingRequest;

class CurrentPasswordGuard
{
    public static function required(bool $force = false): bool
    {
        return $force || (bool) (setting('Backend\Auth')->sensitivePasswordConfirmation ?? true);
    }

    public function check(string $adminUuid, IncomingRequest $request, bool $force = false): ?array
    {
        if ( ! self::required($force)):
            return null;
        endif;

        $password = $request->getPost('currentPassword');
        if ( ! is_string($password) || $password === ''):
            $message = lang('backend/account.errors.currentPasswordRequired');
            return ['result' => false, 'errors' => ['currentPassword' => $message], 'message' => $message, 'status' => 200];
        endif;

        $config = setting('Backend\Auth');
        $throttler = service('throttler');
        $key = 'account-current-password-' . hash('sha256', $adminUuid . '|' . $request->getIPAddress());
        if ( ! $throttler->check($key, (int) $config->attemptsLimit, (int) $config->attemptsInterval, 0)):
            return ['result' => false, 'message' => lang('backend/account.messages.tooManyAttempts'), 'status' => 429];
        endif;

        if ( ! model(AccountModel::class)->verifyCurrentPassword($adminUuid, $password)):
            $throttler->check($key, (int) $config->attemptsLimit, (int) $config->attemptsInterval, 1);
            $message = lang('backend/account.errors.currentPasswordInvalid');
            return ['result' => false, 'errors' => ['currentPassword' => $message], 'message' => $message, 'status' => 200];
        endif;

        return null;
    }

    /** L'autorizzazione vale soltanto per il trasferimento corrente e per questa sessione. */
    public function checkOperation(string $adminUuid, IncomingRequest $request, string $operationId): ?array
    {
        if ( ! self::required()):
            return null;
        endif;

        $key = $this->operationKey($adminUuid, $request, $operationId);
        $grants = session()->get('currentPasswordOperations') ?? [];
        if ($key !== null && isset($grants[$key]) && $grants[$key] >= time()):
            $this->rememberOperation($adminUuid, $request, $operationId);
            return null;
        endif;

        $error = $this->check($adminUuid, $request);
        if ($error === null && $key !== null):
            $this->rememberOperation($adminUuid, $request, $operationId);
        endif;

        return $error;
    }

    public function rememberOperation(string $adminUuid, IncomingRequest $request, string $operationId): void
    {
        if ( ! self::required()):
            return;
        endif;

        $key = $this->operationKey($adminUuid, $request, $operationId);
        if ($key === null):
            return;
        endif;

        $grants = array_filter(session()->get('currentPasswordOperations') ?? [], static fn(int $expires): bool => $expires >= time());
        $grants[$key] = time() + 900;
        session()->set('currentPasswordOperations', array_slice($grants, -64, null, true));
    }

    private function operationKey(string $adminUuid, IncomingRequest $request, string $operationId): ?string
    {
        if (preg_match('/^[a-f0-9]{64}$/', $operationId) !== 1):
            return null;
        endif;

        $fingerprint = service('authorization')->currentAccessFingerprint();
        if ( ! is_string($fingerprint) || $fingerprint === ''):
            return null;
        endif;

        return hash('sha256', $adminUuid . '|' . $fingerprint . '|' . $request->getUri()->getPath() . '|' . $operationId);
    }
}
