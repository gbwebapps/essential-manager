<?php declare(strict_types = 1);

namespace App\Models\Backend\Components;

use App\Models\Backend\BackendModel;

/** Contratto del dominio per il trasporto XLSX di entità collegate. */
abstract class TransferModel extends BackendModel
{
    abstract public function key(): string;
    abstract public function tables(): array;
    abstract public function requiredFields(string $table): array;
    abstract public function textFields(): array;
    abstract public function getPackageImportStructures(): array;
    abstract public function getPackageData(): array;
    abstract public function planPackage(array $workbook, array $generatedUuids = [], ?array $snapshot = null): array;
    abstract public function validatePreparedChunk(array $planned, int $offset): array;
    abstract public function executePackagePlan(array $workbook, array $staged, callable $backup): array;
    abstract public function createPackageBackup(string $importId, array $snapshot): array;

    public function chunkSize(): int
    {
        return 5;
    }

    public function message(string $key): string
    {
        return lang('backend/' . $this->key() . '.' . $key);
    }

    public function getPackageTemplate(): array
    {
        $package = [];
        foreach ($this->getPackageImportStructures() as $table => $structure):
            $package[$table] = ['headers' => array_column($structure, 'name'), 'rows' => []];
        endforeach;
        return $package;
    }

    public function getPackageTables(): array
    {
        return $this->tables();
    }
}
