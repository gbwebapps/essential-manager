<?php declare(strict_types = 1);

namespace App\Libraries;

class SettingsSecrets
{
    public const PREFIX = 'enc:v1:';

    public function encrypt(string $value): string
    {
        return $value === '' ? '' : self::PREFIX . base64_encode(service('encrypter')->encrypt($value));
    }

    public function decrypt(string $value): string
    {
        if ($value === '' || ! str_starts_with($value, self::PREFIX)):
            return $value;
        endif;

        $payload = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if ($payload === false):
            throw new \RuntimeException('Impostazione SMTP cifrata non valida.');
        endif;

        try {
            return service('encrypter')->decrypt($payload);
        } catch (\Throwable $error) {
            throw new \RuntimeException('Impossibile decifrare la password SMTP: verificare la chiave locale.', 0, $error);
        }
    }
}
