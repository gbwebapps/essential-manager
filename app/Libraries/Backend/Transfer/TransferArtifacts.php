<?php declare(strict_types = 1);

namespace App\Libraries\Backend\Transfer;

/** Rimuove solo artefatti di esportazione scaduti e riconosciuti; nessun backup viene eliminato. */
final class TransferArtifacts
{
    public static function cleanup(string $root, bool $jobs): void
    {
        foreach (glob($root . '*', GLOB_ONLYDIR) ?: [] as $directory):
            if (is_link($directory) || preg_match('/^[a-f0-9]{64}$/', basename($directory)) !== 1):
                continue;
            endif;
            $lock = $jobs ? @fopen($directory . '/export.lock', 'c+') : null;
            if ($jobs && ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB))):
                if (is_resource($lock)):
                    fclose($lock);
                endif;
                continue;
            endif;
            try {
                $manifestPath = $directory . '/manifest.json';
                $manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath), true) : null;
                if ( ! is_array($manifest) || ! is_int($manifest['expiresAt'] ?? null) || $manifest['expiresAt'] >= time()):
                    continue;
                endif;
                $files = ['manifest.json', 'manifest.json.tmp', 'snapshot.json', 'snapshot.json.tmp', 'prepared.jsonl', 'package.xlsx', 'package.xlsx.tmp'];
                $name = $manifest['fileName'] ?? null;
                if (is_string($name) && basename($name) === $name && preg_match('/^[a-z0-9_-]+\.xlsx$/i', $name) === 1):
                    $files[] = $name;
                    $files[] = $name . '.tmp';
                endif;
                foreach (array_unique($files) as $file):
                    if (is_file($directory . '/' . $file)):
                        unlink($directory . '/' . $file);
                    endif;
                endforeach;
            } finally {
                if (is_resource($lock)):
                    flock($lock, LOCK_UN);
                    fclose($lock);
                endif;
            }
            if ($jobs && is_file($directory . '/export.lock')):
                unlink($directory . '/export.lock');
            endif;
            @rmdir($directory);
        endforeach;
    }
}
