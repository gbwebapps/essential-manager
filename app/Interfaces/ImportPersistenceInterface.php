<?php declare(strict_types = 1);

namespace App\Interfaces;

interface ImportPersistenceInterface extends ImportValidationInterface
{
    /** Tabelle collegate modificate dal processo, da includere nello stesso snapshot preventivo. */
    public function importBackupTables(): array;

    /** Scrive nella transazione del chunk riutilizzando il processo applicativo del CRUD. */
    public function saveImportedRow(array $data, string $action, ?array $currentRow): array;

    /** Invia una notifica solo dopo il commit effettivo e la persistenza del progresso. */
    public function sendImportNotification(array $notification): bool;
}
