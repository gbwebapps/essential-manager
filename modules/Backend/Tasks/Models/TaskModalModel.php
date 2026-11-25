<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Models;

use Modules\Backend\Notes\Models\NotesModel;

/** Salvataggio atomico del task e delle note in bozza, nei modali e nei form dedicati. */
class TaskModalModel extends TasksModel
{
    /**
     * Legge un task nel suo contesto; le note richiedono un permesso distinto.
     * @param string $uuid UUID del task.
     * @param string $column Colonna interna project_uuid oppure application_uuid.
     * @param string $ownerUuid UUID del proprietario autorizzato.
     * @param bool $readNotes Permesso server di lettura note.
     * @return object|null Record con passi e note oppure null se non disponibile.
     */
    public function getModal(string $uuid, string $column, string $ownerUuid, bool $readNotes): ?object
    {
        $table = $this->modalOwnerTable($column);
        if ($column === 'is_studio' && $ownerUuid !== '1'): throw new \InvalidArgumentException('Invalid Studio owner.'); endif;
        $extra = $column === 'application_uuid' ? ' and exists (select 1 from projects where uuid=owner.project_uuid and deleted_at is null)' : '';
        $task = $column === 'is_studio'
            ? $this->db->query('select uuid from tasks where uuid=? and is_studio=1 and deleted_at is null', [$uuid])->getRow()
            : $this->db->query("select tasks.uuid from tasks inner join {$table} owner on owner.uuid=tasks.{$column} where tasks.uuid=? and tasks.{$column}=? and tasks.deleted_at is null and owner.deleted_at is null{$extra}", [$uuid, $ownerUuid])->getRow();
        if ( ! $task): return null; endif;
        $result = $this->getByUUID($uuid);
        if ( ! $result['result']): return null; endif;
        $record = $result['row'];
        $record->notes = [];
        $steps = [];
        foreach ($record->steps as $step): $step->notes = []; $steps[$step->uuid] = $step; endforeach;
        if ($readNotes):
            $notes = $this->db->query('select n.uuid,n.content,n.step_uuid from notes n left join task_steps s on s.uuid=n.step_uuid where n.deleted_at is null and (n.task_uuid=? or s.task_uuid=?) order by n.created_at desc,n.id desc', [$uuid, $uuid])->getResult();
            foreach ($notes as $note):
                $item = (object) ['uuid' => $note->uuid, 'content' => $note->content, 'version' => hash('sha256', $note->content)];
                if ($note->step_uuid === null): $record->notes[] = $item;
                elseif (isset($steps[$note->step_uuid])): $steps[$note->step_uuid]->notes[] = $item; endif;
            endforeach;
        endif;
        return $record;
    }

    /**
     * Valida tutti i campi prima di scrivere e conserva le note omesse dal payload.
     * @param array $posts Form non attendibile; notes contiene soltanto operazioni esplicite.
     * @param string $column Contesto interno ammesso dalla whitelist.
     * @param string $ownerUuid Proprietario controllato dal controller.
     * @param string|null $adminUuid Identità autenticata, mai accettata dal client.
     * @param bool $canComplete Permesso server di completamento/riapertura.
     * @param array<string, bool> $permissions Permessi server distinti delle note.
     * @return array Esito, record aggiornato oppure errori dei campi; nessun salvataggio parziale.
     */
    public function saveModal(array $posts, string $column, string $ownerUuid, ?string $adminUuid, bool $canComplete, array $permissions): array
    {
        $this->modalOwnerTable($column);
        return $this->saveEditor($posts, $column, $ownerUuid, $adminUuid, $canComplete, $permissions, false);
    }

    /**
     * Salva il form completo con contesto, riferimento Studio, nota iniziale e note dei nuovi passi.
     * @param array $posts Dati non attendibili, validati insieme alle note prima della transazione.
     * @param object|null $original Task autorizzato dal controller, null per Add.
     * @param string|null $adminUuid Amministratore autenticato.
     * @param bool $canComplete Permesso effettivo di completamento.
     * @param array<string, bool> $permissions Permessi delle note, stabiliti dal server.
     * @return array Esito atomico oppure errori di tutti i campi.
     */
    public function saveForm(array $posts, ?object $original, ?string $adminUuid, bool $canComplete, array $permissions): array
    {
        return $this->saveEditor($posts, '', '', $adminUuid, $canComplete, $permissions, true, $original);
    }

    /**
     * Valida le operazioni sulle note e le applica nella stessa transazione di task e passi.
     * @param array $posts Payload dell'editor.
     * @param string $column Contesto interno del modale; ricavato dal form nelle pagine dedicate.
     * @param string $ownerUuid Proprietario autorizzato.
     * @param string|null $adminUuid Identità autenticata.
     * @param bool $canComplete Permesso di completamento.
     * @param array $permissions Permessi server delle note.
     * @param bool $standalone True per il form completo Add/Edit.
     * @param object|null $original Task autorizzato in modifica.
     * @return array Esito senza scritture parziali.
     */
    private function saveEditor(array $posts, string $column, string $ownerUuid, ?string $adminUuid, bool $canComplete, array $permissions, bool $standalone, ?object $original = null): array
    {
        $editing = $standalone ? $original !== null : (is_string($posts['uuid'] ?? null) && $posts['uuid'] !== '');
        $errors = [];
        [$notes, $stepNotes] = $this->extractNotes($posts, $editing, $permissions, $errors);
        if ( ! $standalone && ! $editing && ($posts['uuid'] ?? null) === ''): unset($posts['uuid']); endif;
        $validated = $standalone ? $this->validateForm($posts, $original) : $this->validatePayload($posts, $editing);
        $errors += $validated['errors'] ?? [];
        if ($errors !== []): return $this->invalidNotes($errors); endif;
        if ($standalone): [$column, $ownerUuid] = $validated['context']; endif;
        try {
            $this->db->transBegin();
            $saved = $standalone
                ? ($editing ? parent::edit($posts, $adminUuid, $canComplete) : parent::add($posts, $adminUuid, $canComplete))
                : match ($column) {
                'is_studio' => parent::saveForStudio($posts, $adminUuid, $canComplete),
                'project_uuid' => parent::saveForProject($posts, $ownerUuid, $adminUuid, $canComplete),
                'application_uuid' => parent::saveForApplication($posts, $ownerUuid, $adminUuid, $canComplete),
            };
            if ( ! $saved['result'] && ($saved['changed'] ?? null) !== false):
                $this->db->transRollback(); return $saved;
            endif;
            $uuid = $saved['uuid'] ?? $posts['uuid'];
            $record = $this->getModal($uuid, $column, $ownerUuid, false);
            if ($record === null): throw new \RuntimeException('Saved task unavailable.'); endif;
            $changed = $this->applyNotes($notes, 'task_uuid', $uuid, $adminUuid, 'notes', $errors);
            foreach ($validated['data']['steps'] as $index => $step):
                $persisted = $record->steps[$index] ?? null;
                if ( ! $persisted || ($step['uuid'] !== '' && $persisted->uuid !== $step['uuid'])): throw new \RuntimeException('Task step mapping failed.'); endif;
                $changed = $this->applyNotes($stepNotes[$index] ?? [], 'step_uuid', $persisted->uuid, $adminUuid, "steps.{$index}.notes", $errors) || $changed;
            endforeach;
            if ($errors !== []): $this->db->transRollback(); return $this->invalidNotes($errors); endif;
            if ( ! $saved['result'] && ! $changed): $this->db->transCommit(); return $saved; endif;
            if ($changed && ! $saved['result']): $this->modalExecute('update tasks set updated_at=? where uuid=?', [date('Y-m-d H:i:s'), $uuid]); endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Task notes transaction failed.'); endif;
            $record = $this->getModal($uuid, $column, $ownerUuid, $permissions['show'] ?? false);
            if ($record === null): throw new \RuntimeException('Saved task unavailable.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'uuid' => $uuid, 'record' => $record, 'message' => lang('backend/tasks.messages.saveSuccess')];
        } catch (\Throwable $error) {
            $this->db->transRollback(); $this->db->resetTransStatus();
            log_message('error', 'TaskModal::saveModal - ' . $error->getMessage());
            return ['result' => false, 'message' => lang('backend/tasks.messages.saveError')];
        }
    }

    /**
     * Valida una nuova bozza completa senza scrivere record o accettare identificativi persistiti.
     * @param array $posts Campi del task con eventuali note del task e dei passi.
     * @param array<string, bool> $permissions Permessi server delle note.
     * @return array Dati normalizzati riapribili nell'editor oppure errori aggregati.
     */
    public function validateDraftPayload(array $posts, array $permissions): array
    {
        $errors = [];
        [$notes, $stepNotes] = $this->extractNotes($posts, false, $permissions, $errors);
        $checked = $this->validatePayload($posts);
        $errors += $checked['errors'] ?? [];
        if ($errors !== []): return $this->invalidNotes($errors); endif;
        $data = $checked['data'];
        $data['notes'] = $notes;
        foreach ($data['steps'] as $index => &$step): $step['notes'] = $stepNotes[$index] ?? []; endforeach;
        unset($step);
        return ['result' => true, 'data' => $data];
    }

    /**
     * Separa e valida tutte le note lasciando al validatore Tasks i campi del task e dei passi.
     * @param array $posts Payload modificato per riferimento, privato delle note.
     * @param bool $editing True soltanto per proprietari già persistiti.
     * @param array $permissions Permessi server, mai letti dal payload.
     * @param array $errors Errori aggregati per riferimento.
     * @return array{0:array,1:array} Operazioni sul task e sui passi, indicizzate per posizione.
     */
    private function extractNotes(array &$posts, bool $editing, array $permissions, array &$errors): array
    {
        $seen = []; $total = 0;
        $notes = $this->validateNotes(array_key_exists('notes', $posts) ? $posts['notes'] : [], 'notes', $editing, $permissions, $errors, $seen, $total);
        unset($posts['notes']);
        $stepNotes = [];
        if (is_array($posts['steps'] ?? null) && array_is_list($posts['steps']) && count($posts['steps']) <= 100):
            foreach ($posts['steps'] as $index => &$step):
                if ( ! is_array($step)): continue; endif;
                $stepNotes[$index] = $this->validateNotes(array_key_exists('notes', $step) ? $step['notes'] : [], "steps.{$index}.notes", $editing && is_string($step['uuid'] ?? null) && $step['uuid'] !== '', $permissions, $errors, $seen, $total);
                unset($step['notes']);
            endforeach;
            unset($step);
        endif;
        return [$notes, $stepNotes];
    }

    /**
     * Controlla struttura, contenuti, permessi e duplicati di una lista di operazioni.
     * @param mixed $items Valore POST non attendibile.
     * @param string $path Percorso dei campi nel form.
     * @param bool $existingOwner True soltanto per task/passi già esistenti.
     * @param array $permissions Permessi del server.
     * @param array $errors Errori aggregati per riferimento.
     * @param array $seen UUID già incontrati per riferimento.
     * @param int $total Numero globale di operazioni per riferimento, massimo 100.
     * @return array Operazioni normalizzate; gli errori impediscono ogni scrittura.
     */
    private function validateNotes(mixed $items, string $path, bool $existingOwner, array $permissions, array &$errors, array &$seen, int &$total): array
    {
        $invalid = lang('backend/notes.messages.invalidData');
        if ( ! is_array($items) || ! array_is_list($items) || count($items) > 100 || ($total += count($items)) > 100): $errors[$path] = $invalid; return []; endif;
        $result = [];
        foreach ($items as $index => $item):
            $field = "{$path}.{$index}.content";
            if ( ! is_array($item) || array_diff(array_keys($item), ['uuid', 'version', 'content', 'deleted']) !== []): $errors[$field] = $invalid; continue; endif;
            $existing = isset($item['uuid']);
            $deleted = array_key_exists('deleted', $item);
            if ($existing && ( ! $existingOwner || ! is_string($item['uuid']) || ! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $item['uuid']) || isset($seen[strtolower($item['uuid'])]) || ! is_string($item['version'] ?? null) || ! preg_match('/^[a-f0-9]{64}$/', $item['version']))): $errors[$field] = $invalid; continue; endif;
            if ( ! $existing && array_key_exists('version', $item)): $errors[$field] = $invalid; continue; endif;
            if ($deleted && ( ! $existing || $item['deleted'] !== '1' || array_key_exists('content', $item))): $errors[$field] = $invalid; continue; endif;
            $permission = $existing ? ($deleted ? 'delete' : 'edit') : 'add';
            if ( ! ($permissions[$permission] ?? false) || ($existing && ! ($permissions['show'] ?? false))): $errors[$field] = lang('backend/tasks.messages.forbidden'); continue; endif;
            if ($existing): $seen[strtolower($item['uuid'])] = true; endif;
            if ( ! $deleted):
                $valid = model(NotesModel::class)->validatePayload(['content' => $item['content'] ?? null]);
                if ( ! $valid['result']): $errors[$field] = $valid['errors']['content'] ?? $invalid; continue; endif;
                $item['content'] = $valid['data']['content'];
            endif;
            $result[$index] = $item;
        endforeach;
        return $result;
    }

    /**
     * Applica operazioni a un solo proprietario bloccato dalla transazione del task.
     * @param array $operations Operazioni già validate.
     * @param string $column task_uuid oppure step_uuid, scelto dal codice.
     * @param string $uuid UUID persistito del proprietario.
     * @param string|null $adminUuid Identità autenticata.
     * @param string $path Percorso degli errori nel form.
     * @param array $errors Errori di appartenenza/versione aggregati per riferimento.
     * @return bool True se almeno una nota cambia; omissione significa conservazione.
     */
    private function applyNotes(array $operations, string $column, string $uuid, ?string $adminUuid, string $path, array &$errors): bool
    {
        $changed = false;
        foreach ($operations as $index => $note):
            if ( ! isset($note['uuid'])):
                $model = model(NotesModel::class);
                if ($column === 'task_uuid'): $model->insertForTask($note, $uuid, $adminUuid);
                else: $model->insertForStep($note, $uuid, $adminUuid); endif;
                $changed = true; continue;
            endif;
            $original = $this->db->query("select content from notes where uuid=? and {$column}=? and deleted_at is null for update", [$note['uuid'], $uuid])->getRow();
            if ( ! $original || ! hash_equals(hash('sha256', $original->content), $note['version'])):
                $errors["{$path}.{$index}.content"] = lang('backend/tasks.modal.noteChanged'); continue;
            endif;
            if (isset($note['deleted'])):
                $this->modalExecute("delete from notes where uuid=? and {$column}=?", [$note['uuid'], $uuid]); $changed = true;
            elseif ($original->content !== $note['content']):
                $this->modalExecute("update notes set content=?,updated_at=? where uuid=? and {$column}=?", [$note['content'], date('Y-m-d H:i:s'), $note['uuid'], $uuid]); $changed = true;
            endif;
        endforeach;
        return $changed;
    }

    /** @param array $errors Errori dei campi. @return array Risposta standard senza dettagli nell'alert. */
    private function invalidNotes(array $errors): array
    {
        return ['result' => false, 'errors' => $errors, 'message' => lang('backend/tasks.messages.invalidData')];
    }

    /** @param string $column Contesto interno. @return string|null Tabella del proprietario. */
    private function modalOwnerTable(string $column): ?string
    {
        return match ($column) { 'project_uuid' => 'projects', 'application_uuid' => 'applications', 'is_studio' => null, default => throw new \InvalidArgumentException('Invalid task owner.') };
    }

    /** @param string $sql Query interna parametrizzata. @param array $values Valori associati. @return void */
    private function modalExecute(string $sql, array $values): void
    {
        if ($this->db->query($sql, $values) === false): throw new \RuntimeException('Task note write failed.'); endif;
    }
}
