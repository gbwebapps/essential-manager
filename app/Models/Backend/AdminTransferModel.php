<?php declare(strict_types = 1);

namespace App\Models\Backend;

use App\Models\Backend\Components\TransferModel;
use App\Libraries\Backend\Transfer\TransferRecoveryException;
use Config\Backend\AdminTransfer;

/** Pianifica il trasferimento senza scritture; applica le differenze dopo backup e nuova validazione. */
class AdminTransferModel extends TransferModel
{
    private array $comparisonCache = [];
    private array $collations = [];
    private array $backupPlan = [];

    public function key(): string
    {
        return 'admintransfer';
    }

    public function tables(): array
    {
        return AdminTransfer::TABLES;
    }

    public function textFields(): array
    {
        return ['uuid', 'firstname', 'lastname', 'email', 'phone', 'note', 'name', 'description', 'permission', 'admin_uuid'];
    }

    public function requiredFields(string $table): array
    {
        return match ($table) {
            'admins_groups' => ['id', 'uuid'],
            'admins' => ['uuid', 'group_id'],
            default => AdminTransfer::FIELDS[$table],
        };
    }

    public function getPackageImportStructures(): array
    {
        $structures = [];
        foreach (AdminTransfer::FIELDS as $table => $fields):
            $metadata = [];
            foreach ($this->db->getFieldData($table) as $field):
                $metadata[$field->name] = $field;
            endforeach;
            foreach ($fields as $name):
                if ( ! isset($metadata[$name])):
                    throw new \RuntimeException('Schema AdminTransfer non compatibile: ' . $table . '.' . $name);
                endif;
                $field = $metadata[$name];
                $structures[$table][] = [
                    'name' => $name, 'type' => $field->type,
                    'max_length' => $field->max_length ?? null,
                    'nullable' => $field->nullable ?? false, 'default' => null,
                    'primary_key' => (int) ($field->primary_key ?? 0), 'is_index' => false,
                ];
            endforeach;
        endforeach;
        return $structures;
    }

    /** Snapshot privato completo: le colonne riservate non raggiungono mai il workbook o la preview. */
    public function readPackageSnapshot(bool $lock = false): array
    {
        $snapshot = [];
        foreach ($this->tables() as $table):
            $order = $table === 'admins' ? 'uuid' : 'id';
            $query = $this->db->query('select * from ' . $table . ' order by ' . $order . ($lock ? ' for update' : ''));
            if ($query === false):
                throw new \RuntimeException('Lettura snapshot AdminTransfer non riuscita.');
            endif;
            $snapshot[$table] = $query->getResultArray();
        endforeach;
        return $snapshot;
    }

    public function getPackageData(): array
    {
        if ($this->db->transDepth !== 0 || $this->db->query('set transaction isolation level repeatable read') === false || ! $this->db->transBegin()):
            throw new \RuntimeException('Snapshot AdminTransfer non disponibile.');
        endif;
        try {
            $snapshot = $this->readPackageSnapshot();
            $package = $this->getPackageTemplate();
            $included = [];
            foreach ($snapshot['admins'] as $row):
                if ((int) $row['superadmin'] === 0 && $row['deleted_at'] === null):
                    $included[$row['uuid']] = true;
                endif;
            endforeach;
            foreach ($snapshot as $table => $rows):
                foreach ($rows as $row):
                    if (($table === 'admins' && ! isset($included[$row['uuid']]))
                        || ($table === 'admins_permissions' && ! isset($included[$row['admin_uuid']]))):
                        continue;
                    endif;
                    $package[$table]['rows'][] = array_intersect_key($row, array_flip(AdminTransfer::FIELDS[$table]));
                endforeach;
            endforeach;
            if ( ! $this->db->transStatus() || ! $this->db->transCommit()):
                throw new \RuntimeException('Snapshot AdminTransfer non completato.');
            endif;
            return $package;
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    /** Usa la stessa equivalenza dei vincoli UNIQUE, inclusi accenti, maiuscole e spazi finali. */
    protected function comparisonKey(string $table, string $field, string $value): string
    {
        $cacheKey = $table . '.' . $field . ':' . $value;
        if (isset($this->comparisonCache[$cacheKey])):
            return $this->comparisonCache[$cacheKey];
        endif;
        $column = $table . '.' . $field;
        if ( ! isset($this->collations[$column])):
            $metadata = $this->db->query('show full columns from ' . $table . ' where field = ?', [$field]);
            $this->collations[$column] = $metadata === false ? '' : (string) ($metadata->getRowArray()['Collation'] ?? '');
        endif;
        $collation = $this->collations[$column];
        if (preg_match('/^[a-z0-9_]+$/i', $collation) !== 1):
            throw new \RuntimeException('Collation AdminTransfer non disponibile.');
        endif;
        $charset = explode('_', $collation, 2)[0];
        $expression = 'convert(? using ' . $charset . ')';
        $expression = str_contains($collation, '_nopad_') ? $expression : 'rtrim(' . $expression . ')';
        $query = $this->db->query('select hex(weight_string(' . $expression . ' collate ' . $collation . ')) as comparison_key', [$value]);
        if ($query === false):
            throw new \RuntimeException('Confronto AdminTransfer non disponibile.');
        endif;
        return $this->comparisonCache[$cacheKey] = (string) $query->getRowArray()['comparison_key'];
    }

    protected function permissionKeys(): array
    {
        $keys = [];
        foreach (config(\Config\Backend\Permissions::class)->getPermissions() as $group):
            array_push($keys, ...array_keys($group['perms']));
        endforeach;
        sort($keys);
        return array_values(array_unique($keys));
    }

    private function schemaHash(): string
    {
        return hash('sha256', json_encode([$this->getPackageImportStructures(), $this->permissionKeys()], JSON_THROW_ON_ERROR));
    }

    private function snapshotHash(array $snapshot): string
    {
        return hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR));
    }

    /** Nessun ID del file sostituisce un ID locale. I gruppi e gli amministratori si riconoscono tramite UUID. */
    public function planPackage(array $workbook, array $generatedUuids = [], ?array $snapshot = null): array
    {
        $this->comparisonCache = [];
        $snapshot ??= $this->readPackageSnapshot();
        $errors = [];
        $operations = array_fill_keys($this->tables(), []);
        $sheets = [];
        $totals = ['insert' => 0, 'update' => 0, 'skip' => 0];
        $groups = $groupNames = $admins = $emails = $groupPermissions = $exceptions = [];
        foreach ($snapshot['admins_groups'] as $row):
            $groups[strtolower($row['uuid'])] = $row;
            $groupNames[$this->comparisonKey('admins_groups', 'name', $row['name'])] = strtolower($row['uuid']);
        endforeach;
        foreach ($snapshot['admins'] as $row):
            $admins[strtolower($row['uuid'])] = $row;
            $emails[$this->comparisonKey('admins', 'email', $row['email'])] = strtolower($row['uuid']);
        endforeach;
        foreach ($snapshot['admins_groups_permissions'] as $row):
            $groupPermissions[$row['group_id'] . ':' . $row['permission']] = $row;
        endforeach;
        foreach ($snapshot['admins_permissions'] as $row):
            $exceptions[strtolower($row['admin_uuid']) . ':' . $row['permission']] = $row;
        endforeach;
        $groupMap = $adminMap = $seenNames = $seenEmails = $seenGroupUuids = [];
        $knownPermissions = array_fill_keys($this->permissionKeys(), true);

        foreach ($this->tables() as $table):
            $headers = $workbook[$table]['headers'] ?? [];
            $sheet = ['headers' => $headers, 'rows' => [], 'plan' => ['insert' => 0, 'update' => 0, 'skip' => 0]];
            if ( ! is_array($headers) || array_diff($this->requiredFields($table), $headers) !== []
                || array_diff($headers, AdminTransfer::FIELDS[$table]) !== [] || count(array_unique($headers)) !== count($headers)
                || ! is_array($workbook[$table]['rows'] ?? null)):
                $errors[] = sprintf($this->message('messages.invalidHeaders'), $table);
                $sheets[$table] = $sheet;
                continue;
            endif;
            $seen = [];
            foreach ($workbook[$table]['rows'] as $index => $input):
                $line = (int) ($input['_row'] ?? ($index + 2));
                $data = array_intersect_key($input, array_flip($headers));
                $before = [];
                $identity = '';
                $localId = null;
                $rowErrors = [];
                if ($table === 'admins_groups'):
                    $fileId = (string) ($data['id'] ?? '');
                    $rawUuid = (string) ($data['uuid'] ?? '');
                    $uuidKey = 'admins_groups:' . $line;
                    $uuid = $rawUuid === '' ? ($generatedUuids[$uuidKey] ?? $this->generateUUID()) : strtolower($rawUuid);
                    if ($rawUuid === ''):
                        $generatedUuids[$uuidKey] = $uuid;
                    endif;
                    if ( ! $this->positiveId($fileId) || preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid) !== 1):
                        $rowErrors[] = sprintf($this->message('messages.invalidCell'), $table, $line, 'id/uuid');
                    endif;
                    if (isset($seenGroupUuids[$uuid])):
                        $rowErrors[] = sprintf($this->message('messages.duplicateValue'), $table, $line, 'uuid');
                    endif;
                    $seenGroupUuids[$uuid] = true;
                    $before = $groups[$uuid] ?? [];
                    $localId = $before === [] ? null : (int) $before['id'];
                    $data = array_replace(['description' => ''], array_intersect_key($before, array_flip(AdminTransfer::FIELDS[$table])), $data, ['uuid' => $uuid]);
                    $nameKey = $this->comparisonKey('admins_groups', 'name', (string) ($data['name'] ?? ''));
                    if (isset($seenNames[$nameKey])):
                        $rowErrors[] = sprintf($this->message('messages.duplicateValue'), $table, $line, 'name');
                    endif;
                    if (isset($groupNames[$nameKey]) && $groupNames[$nameKey] !== $uuid):
                        $rowErrors[] = sprintf($this->message('messages.groupNameConflict'), (string) $data['name'], $line);
                    endif;
                    $seenNames[$nameKey] = true;
                    $identity = $fileId;
                    $groupMap[$fileId] = $localId;
                elseif ($table === 'admins'):
                    $rawUuid = (string) ($data['uuid'] ?? '');
                    $uuid = $rawUuid === '' ? ($generatedUuids[(string) $line] ?? $this->generateUUID()) : strtolower($rawUuid);
                    if ($rawUuid === ''):
                        $generatedUuids[(string) $line] = $uuid;
                    endif;
                    if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid) !== 1):
                        $rowErrors[] = sprintf($this->message('messages.invalidCell'), $table, $line, 'uuid');
                    endif;
                    $before = $admins[$uuid] ?? [];
                    if ($before !== [] && ((int) $before['superadmin'] === 1 || $before['deleted_at'] !== null)):
                        $rowErrors[] = $this->message('messages.protectedAdmin');
                    endif;
                    $fileGroup = (string) ($data['group_id'] ?? '');
                    if ( ! array_key_exists($fileGroup, $groupMap)):
                        $rowErrors[] = sprintf($this->message('messages.invalidRelation'), $table, $line);
                    endif;
                    $data = array_replace(['note' => null], array_intersect_key($before, array_flip(AdminTransfer::FIELDS[$table])), $data, ['uuid' => $uuid]);
                    $emailKey = $this->comparisonKey('admins', 'email', (string) ($data['email'] ?? ''));
                    if (isset($seenEmails[$emailKey]) || (isset($emails[$emailKey]) && $emails[$emailKey] !== $uuid)):
                        $rowErrors[] = sprintf($this->message('messages.duplicateValue'), $table, $line, 'email');
                    endif;
                    $seenEmails[$emailKey] = true;
                    $identity = $uuid;
                    $adminMap[$uuid] = true;
                else:
                    $permission = (string) ($data['permission'] ?? '');
                    if ( ! isset($knownPermissions[$permission])):
                        $rowErrors[] = sprintf($this->message('messages.unknownPermission'), $permission, $table, $line);
                    endif;
                    if ($table === 'admins_groups_permissions'):
                        $fileGroup = (string) ($data['group_id'] ?? '');
                        if ( ! array_key_exists($fileGroup, $groupMap)):
                            $rowErrors[] = sprintf($this->message('messages.invalidRelation'), $table, $line);
                        endif;
                        $identity = $fileGroup . ':' . $permission;
                        $localGroup = $groupMap[$fileGroup] ?? null;
                        $before = $localGroup === null ? [] : ($groupPermissions[$localGroup . ':' . $permission] ?? []);
                    else:
                        $uuid = strtolower((string) ($data['admin_uuid'] ?? ''));
                        $data['admin_uuid'] = $uuid;
                        if ( ! isset($adminMap[$uuid])):
                            $rowErrors[] = sprintf($this->message('messages.invalidRelation'), $table, $line);
                        endif;
                        if ( ! in_array((string) ($data['allow'] ?? ''), ['0', '1'], true)):
                            $rowErrors[] = sprintf($this->message('messages.invalidCell'), $table, $line, 'allow');
                        endif;
                        $identity = $uuid . ':' . $permission;
                        $before = $exceptions[$identity] ?? [];
                    endif;
                endif;
                if (isset($seen[$identity])):
                    $rowErrors[] = sprintf($this->message('messages.duplicateValue'), $table, $line, $identity);
                endif;
                $seen[$identity] = true;
                $action = $before === [] ? 'insert' : 'update';
                if ($table === 'admins_groups' || $table === 'admins'):
                    array_push($rowErrors, ...$this->validateDataRow($table, $data, $action, $line));
                endif;
                $changed = [];
                foreach ($data as $field => $value):
                    if ($field === 'id' || ($table === 'admins_groups_permissions' && $field === 'group_id')):
                        continue;
                    endif;
                    $comparable = $table === 'admins' && $field === 'group_id' ? ($groupMap[(string) $value] ?? 'new') : $value;
                    if ((string) $comparable !== (string) ($before[$field] ?? null)):
                        $changed[] = $field;
                    endif;
                endforeach;
                if ($before !== [] && $changed === []):
                    $action = 'skip';
                endif;
                array_push($errors, ...$rowErrors);
                $operation = ['action' => $action, 'data' => $data, 'line' => $line, 'localId' => $localId];
                $operations[$table][] = $operation;
                $sheet['plan'][$action]++;
                $totals[$action]++;
                if ($action !== 'skip' && count($sheet['rows']) < 50):
                    $sheet['rows'][] = ['record' => $data, 'action' => $action, 'changed' => $changed];
                endif;
            endforeach;
            $sheets[$table] = $sheet;
        endforeach;
        if ($errors !== []):
            return ['result' => false, 'validationErrors' => array_slice(array_unique($errors), 0, 500)];
        endif;
        return [
            'result' => true, 'plan' => $totals, 'sheets' => $sheets, 'operations' => $operations,
            'generatedUuids' => $generatedUuids, 'baseline' => $this->snapshotHash($snapshot), 'schemaHash' => $this->schemaHash(),
        ];
    }

    private function positiveId(string $value): bool
    {
        return preg_match('/^[1-9][0-9]{0,9}$/', $value) === 1 && (int) $value <= 4294967295;
    }

    /** Riutilizza le regole CRUD, risolvendo unicità e riferimenti sull'intero pacchetto. */
    protected function validateDataRow(string $table, array $data, string $action, int $line): array
    {
        $model = $table === 'admins' ? model(AdminsModel::class) : model(GroupsModel::class);
        $rules = $action === 'insert' ? $model->addValidationRules() : $model->editValidationRules($data);
        unset($rules['permissions.*'], $rules['permissions'], $rules['images']);
        foreach ($rules as &$definition):
            $definition['rules'] = array_values(array_filter($definition['rules'], static fn(string $rule): bool => ! str_starts_with($rule, 'is_unique[') && ! str_starts_with($rule, 'is_not_unique[')));
        endforeach;
        unset($definition);
        if ($table === 'admins'):
            $rules['group_id']['rules'] = ['required', 'is_natural_no_zero'];
        endif;
        $validator = \Config\Services::validation(null, false);
        $validator->setRules($rules);
        if ($validator->run($data)):
            return [];
        endif;
        return array_map(static fn(string $error): string => $table . ' [' . $line . ']: ' . $error, array_values($validator->getErrors()));
    }

    public function validatePreparedChunk(array $planned, int $offset): array
    {
        if ( ! hash_equals($planned['schemaHash'], $this->schemaHash())):
            return ['result' => false, 'processed' => $offset, 'validationErrors' => [$this->message('messages.databaseChanged')]];
        endif;
        $position = $processed = 0;
        $errors = [];
        foreach ($planned['operations'] as $table => $operations):
            foreach ($operations as $operation):
                if ($operation['action'] === 'skip' || $position++ < $offset):
                    continue;
                endif;
                if ($table === 'admins' || $table === 'admins_groups'):
                    array_push($errors, ...$this->validateDataRow($table, $operation['data'], $operation['action'], $operation['line']));
                endif;
                $processed++;
                if ($processed === $this->chunkSize()):
                    break 2;
                endif;
            endforeach;
        endforeach;
        return ['result' => $errors === [], 'processed' => $offset + $processed, 'validationErrors' => $errors];
    }

    public function executePackagePlan(array $workbook, array $staged, callable $backup): array
    {
        if ($this->db->transDepth !== 0 || $this->db->query('set transaction isolation level repeatable read') === false || ! $this->db->transBegin()):
            throw new \RuntimeException('Transazione AdminTransfer non disponibile.');
        endif;
        $notifications = [];
        try {
            $snapshot = $this->readPackageSnapshot(true);
            if ( ! hash_equals($staged['baseline'], $this->snapshotHash($snapshot)) || ! hash_equals($staged['schemaHash'], $this->schemaHash())):
                throw new \DomainException($this->message('messages.databaseChanged'));
            endif;
            $fresh = $this->planPackage($workbook, $staged['generatedUuids'], $snapshot);
            if ( ! $fresh['result'] || $fresh['operations'] !== $staged['operations'] || $fresh['plan'] !== $staged['plan']):
                throw new \DomainException($this->message('messages.databaseChanged'));
            endif;
            $this->backupPlan = $fresh['operations'];
            $backup($snapshot);
            $groupIds = [];
            $groupsModel = model(GroupsModel::class);
            foreach ($fresh['operations']['admins_groups'] as $operation):
                $data = $operation['data'];
                $id = $operation['localId'];
                if ($operation['action'] !== 'skip'):
                    $permissions = [];
                    foreach ($snapshot['admins_groups_permissions'] as $permission):
                        if ($id !== null && (int) $permission['group_id'] === $id):
                            $permissions[] = $permission['permission'];
                        endif;
                    endforeach;
                    $posts = $data + ['permissions' => $permissions];
                    $result = $id === null ? $groupsModel->add($posts, false, $data['uuid']) : $groupsModel->edit(array_replace($posts, ['id' => $id]), false);
                    if ( ! $result['result']):
                        throw new \RuntimeException((string) $result['message']);
                    endif;
                endif;
                $query = $this->db->query('select id from admins_groups where uuid = ?', [$data['uuid']]);
                $row = $query === false ? null : $query->getRowArray();
                if ($row === null):
                    throw new \RuntimeException('Gruppo AdminTransfer non salvato.');
                endif;
                $groupIds[(string) $data['id']] = (int) $row['id'];
            endforeach;
            foreach ($fresh['operations']['admins'] as $operation):
                if ($operation['action'] === 'skip'):
                    continue;
                endif;
                $data = $operation['data'];
                $data['group_id'] = $groupIds[(string) $data['group_id']];
                $query = $this->db->query('select * from admins where uuid = ?', [$data['uuid']]);
                $current = $query === false ? null : $query->getRowArray();
                $result = model(AdminsModel::class)->saveImportedRow($data, $operation['action'], $current);
                if ( ! $result['result']):
                    throw new \RuntimeException((string) $result['message']);
                endif;
                if (isset($result['notification'])):
                    $notifications[] = $result['notification'];
                endif;
            endforeach;
            foreach (['admins_groups_permissions', 'admins_permissions'] as $table):
                foreach ($fresh['operations'][$table] as $operation):
                    if ($operation['action'] === 'skip'):
                        continue;
                    endif;
                    $data = $operation['data'];
                    if ($table === 'admins_groups_permissions'):
                        $ok = $this->db->query('insert into admins_groups_permissions (group_id, permission) values (?, ?)', [$groupIds[(string) $data['group_id']], $data['permission']]);
                    elseif ($operation['action'] === 'insert'):
                        $ok = $this->db->query('insert into admins_permissions (admin_uuid, permission, allow) values (?, ?, ?)', [$data['admin_uuid'], $data['permission'], (int) $data['allow']]);
                    else:
                        $ok = $this->db->query('update admins_permissions set allow = ? where admin_uuid = ? and permission = ?', [(int) $data['allow'], $data['admin_uuid'], $data['permission']]);
                    endif;
                    if ($ok === false):
                        throw new \RuntimeException('Salvataggio permessi AdminTransfer non riuscito.');
                    endif;
                endforeach;
            endforeach;
            $after = $this->planPackage($workbook, $staged['generatedUuids'], $this->readPackageSnapshot(true));
            if ( ! $after['result'] || $after['plan']['insert'] !== 0 || $after['plan']['update'] !== 0 || ! $this->db->transStatus()):
                throw new \RuntimeException('Verifica finale AdminTransfer non riuscita.');
            endif;
        } catch (\Throwable $error) {
            if ( ! $this->db->transRollback()):
                throw new TransferRecoveryException($this->message('messages.importNeedsReview'), 0, $error);
            endif;
            throw $error;
        }
        try {
            if ( ! $this->db->transCommit()):
                throw new \RuntimeException('Commit AdminTransfer non confermato.');
            endif;
        } catch (\Throwable $error) {
            throw new TransferRecoveryException($this->message('messages.importNeedsReview'), 0, $error);
        }
        $notificationFailures = 0;
        foreach ($notifications as $notification):
            try {
                if ( ! model(AdminsModel::class)->sendImportNotification($notification)):
                    $notificationFailures++;
                endif;
            } catch (\Throwable $error) {
                $notificationFailures++;
                log_message('error', 'Notifica AdminTransfer non inviata: ' . $error->getMessage());
            }
        endforeach;
        return ['inserted' => $fresh['plan']['insert'], 'updated' => $fresh['plan']['update'], 'skipped' => $fresh['plan']['skip'], 'notificationFailures' => $notificationFailures];
    }

    /** Backup privato prima delle scritture. Il ripristino non cancella gli amministratori preesistenti. */
    public function createPackageBackup(string $importId, array $snapshot): array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $importId) !== 1):
            throw new \RuntimeException('ID backup AdminTransfer non valido.');
        endif;
        $directory = WRITEPATH . 'backups/admintransfer/' . $importId . '/';
        if ( ! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)):
            throw new \RuntimeException('Backup AdminTransfer non disponibile.');
        endif;
        $sql = "/* AdminTransfer: ripristino manuale immediato; contiene dati riservati. */\nstart transaction;\n";
        foreach (['admins_permissions', 'admins_groups_permissions', 'admins', 'admins_groups'] as $table):
            foreach ($this->backupPlan[$table] as $operation):
                if ($operation['action'] !== 'insert'):
                    continue;
                endif;
                $data = $operation['data'];
                if ($table === 'admins_permissions'):
                    $sql .= 'delete from admins_permissions where admin_uuid = ' . $this->db->escape($data['admin_uuid']) . ' and permission = ' . $this->db->escape($data['permission']) . ";\n";
                elseif ($table === 'admins_groups_permissions'):
                    $group = array_values(array_filter($this->backupPlan['admins_groups'], static fn(array $item): bool => (string) $item['data']['id'] === (string) $data['group_id']))[0]['data'];
                    $sql .= 'delete from admins_groups_permissions where permission = ' . $this->db->escape($data['permission']) . ' and group_id in (select id from admins_groups where uuid = ' . $this->db->escape($group['uuid']) . ");\n";
                elseif ($table === 'admins'):
                    $sql .= 'delete from admins where uuid = ' . $this->db->escape($data['uuid']) . " and superadmin = 0;\n";
                else:
                    $sql .= 'delete from admins_groups where uuid = ' . $this->db->escape($data['uuid']) . ";\n";
                endif;
            endforeach;
        endforeach;
        foreach ($snapshot as $table => $rows):
            foreach ($rows as $row):
                $columns = array_map(static fn(string $column): string => '`' . $column . '`', array_keys($row));
                $values = array_map(fn(mixed $value): string => $value === null ? 'null' : $this->db->escape($value), array_values($row));
                $updates = array_map(static fn(string $column): string => $column . ' = values(' . $column . ')', $columns);
                $sql .= 'insert into ' . $table . ' (' . implode(', ', $columns) . ') values (' . implode(', ', $values) . ') on duplicate key update ' . implode(', ', $updates) . ";\n";
            endforeach;
        endforeach;
        $sql .= "commit;\n";
        $path = $directory . 'backup.sql';
        if (file_put_contents($path . '.tmp', $sql, LOCK_EX) !== strlen($sql) || ! rename($path . '.tmp', $path)):
            throw new \RuntimeException('Backup AdminTransfer incompleto.');
        endif;
        $hash = hash_file('sha256', $path);
        if ($hash !== hash('sha256', $sql)):
            throw new \RuntimeException('Verifica backup AdminTransfer non riuscita.');
        endif;
        return ['file' => 'backup.sql', 'fileHash' => $hash, 'fileSize' => strlen($sql)];
    }
}
