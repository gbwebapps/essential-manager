<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Models;

use App\Models\Backend\BackendModel;

/**
 * Riferimenti informativi dei task Studio; il contesto proprietario resta sempre Studio.
 */
class StudioReferencesModel extends BackendModel
{
    /**
     * Valida la scelta facoltativa senza accettare colonne o UUID arbitrari.
     * @param mixed $value Selezione vuota oppure project:UUID/application:UUID.
     * @return array|null Coppia tipo/UUID normalizzata, null se non valida.
     */
    public function parse(mixed $value): ?array
    {
        if ($value === ''): return ['', '']; endif;
        if ( ! is_string($value) || ! preg_match('/^(project|application):([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/iD', $value, $parts)): return null; endif;
        return [strtolower($parts[1]), strtolower($parts[2])];
    }

    /**
     * @param array $reference Tipo/UUID normalizzati.
     * @return bool Disponibilità attuale, da ricontrollare sotto lock al salvataggio.
     */
    public function available(array $reference): bool
    {
        if ($reference[0] === ''): return true; endif;
        if ($reference[0] === 'project'):
            return $this->db->query('select uuid from projects where uuid = ? and deleted_at is null', [$reference[1]])->getRow() !== null;
        endif;
        return $this->db->query('select a.uuid from applications a join projects p on p.uuid = a.project_uuid where a.uuid = ? and a.deleted_at is null and p.deleted_at is null', [$reference[1]])->getRow() !== null;
    }

    /**
     * Rilegge in una sola query i riferimenti di una pagina, inclusi quelli cestinati.
     * @param array $tasks Task già caricati dal server.
     * @return void Aggiunge reference a ogni oggetto senza alterarne l'appartenenza.
     */
    public function attach(array $tasks): void
    {
        $studio = array_filter($tasks, static fn(object $task): bool => (int) ($task->is_studio ?? 0) === 1);
        if ($studio === []): return; endif;
        $ids = array_map(static fn(object $task): string => $task->uuid, $studio);
        $rows = $this->db->query('select r.*, p.name project_name, a.name application_name, a.project_uuid origin_project_uuid,
            coalesce(p.deleted_at, a.deleted_at, origin.deleted_at) deleted_at
            from studio_task_references r left join projects p on p.uuid = r.project_uuid
            left join applications a on a.uuid = r.application_uuid left join projects origin on origin.uuid = a.project_uuid
            where r.task_uuid in (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids)->getResult();
        $byTask = [];
        foreach ($rows as $row):
            $row->kind = $row->project_uuid !== null ? 'project' : 'application';
            $row->value = $row->kind . ':' . ($row->project_uuid ?? $row->application_uuid);
            $row->name = $row->project_name ?? $row->application_name;
            $byTask[$row->task_uuid] = $row;
        endforeach;
        foreach ($studio as $task): $task->reference = $byTask[$task->uuid] ?? null; endforeach;
    }

    /**
     * Confronta il riferimento sotto il lock del task, all'interno della transazione comune.
     * @param string $taskUuid Task Studio persistito.
     * @param array $reference Tipo/UUID già validati oppure coppia vuota.
     * @return bool True quando il riferimento è cambiato.
     */
    public function hasChanged(string $taskUuid, array $reference): bool
    {
        $row = $this->db->query('select project_uuid, application_uuid from studio_task_references where task_uuid = ? for update', [$taskUuid])->getRow();
        $current = $row ? [($row->project_uuid !== null ? 'project' : 'application'), $row->project_uuid ?? $row->application_uuid] : ['', ''];
        return $current !== $reference;
    }

    /**
     * Salva o rimuove il richiamo nella transazione del task; verifica disponibilità sotto lock.
     * @param string $taskUuid UUID del task Studio controllato dal chiamante.
     * @param array $reference Tipo/UUID, oppure coppia vuota per rimuovere il riferimento.
     * @return void Nessun commit autonomo o scrittura del progetto/applicazione.
     */
    public function save(string $taskUuid, array $reference): void
    {
        [$kind, $uuid] = $reference;
        if ($kind !== ''):
            if ( ! in_array($kind, ['project', 'application'], true)): throw new \InvalidArgumentException('Invalid reference.'); endif;
            if ($kind === 'application'):
                $app = $this->db->query('select project_uuid from applications where uuid = ?', [$uuid])->getRow();
                if ( ! $app || ! $this->db->query('select uuid from projects where uuid = ? and deleted_at is null for update', [$app->project_uuid])->getRow()): throw new \DomainException(lang('backend/studio.invalidReference')); endif;
            endif;
            $table = $kind === 'project' ? 'projects' : 'applications';
            if ( ! $this->db->query("select uuid from {$table} where uuid = ? and deleted_at is null for update", [$uuid])->getRow()): throw new \DomainException(lang('backend/studio.invalidReference')); endif;
        endif;
        if ($this->db->query('delete from studio_task_references where task_uuid = ?', [$taskUuid]) === false): throw new \RuntimeException('Reference deletion failed.'); endif;
        if ($kind !== '' && $this->db->query('insert into studio_task_references (task_uuid, project_uuid, application_uuid) values (?, ?, ?)', [$taskUuid, $kind === 'project' ? $uuid : null, $kind === 'application' ? $uuid : null]) === false): throw new \RuntimeException('Reference insertion failed.'); endif;
    }
}
