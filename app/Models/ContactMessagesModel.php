<?php declare(strict_types = 1);

namespace App\Models;

/** Stores public contact submissions and exposes them to the administrative inbox. */
class ContactMessagesModel extends BaseModel
{
    public const STATUSES = ['unread', 'read', 'archived'];

    /**
     * Stores a validated contact submission with the default unread status.
     *
     * @param array{name: string, email: string, phone: string, message: string} $message Validated form fields.
     * @return int ID of the stored message, or zero if the write failed.
     */
    public function saveMessage(array $message): int
    {
        return $this->db->table('contact_messages')->insert($message) ? (int) $this->db->insertID() : 0;
    }

    /**
     * Returns the validation rules for the inbox search fields.
     *
     * @return array<string, array{label: string, rules: string}> Rules keyed by request field.
     */
    public function showAllSearchValidationRules(): array
    {
        return [
            'searchFields.name' => ['label' => lang('backend/messages.labels.sender'), 'rules' => 'permit_empty|max_length[120]'],
            'searchFields.email' => ['label' => lang('backend/messages.labels.email'), 'rules' => 'permit_empty|max_length[254]'],
            'searchDates.created_at-from' => ['label' => lang('backend/messages.labels.dateFrom'), 'rules' => 'permit_empty|valid_date[Y-m-d H:i:s]'],
            'searchDates.created_at-to' => ['label' => lang('backend/messages.labels.dateTo'), 'rules' => 'permit_empty|valid_date[Y-m-d H:i:s]'],
        ];
    }

    /**
     * Lists message headers matching the current inbox search.
     *
     * @param string|null $status Optional status filter.
     * @param int $page Requested page number.
     * @param int $perPage Number of rows per page.
     * @param array<string, string> $searchFields Literal sender and email fragments.
     * @param array<string, string> $searchDates Inclusive bounds for the receipt date.
     * @param string $column Validated sort column.
     * @param string $order Validated sort direction.
     * @return array{records: list<array>, pagination: array{page: int, limit: int, totalRows: int}} List data and pagination.
     */
    public function getMessages(?string $status, int $page, int $perPage, array $searchFields, array $searchDates, string $column, string $order): array
    {
        $builder = $this->db->table('contact_messages');
        if ($status !== null):
            $builder->where('status', $status);
        endif;

        foreach (['name', 'email'] as $field):
            $value = trim($searchFields[$field] ?? '');
            if ($value !== ''):
                $builder->like($field, $value);
            endif;
        endforeach;

        foreach (['from' => '>=', 'to' => '<='] as $bound => $operator):
            $value = $searchDates['created_at-' . $bound] ?? '';
            if ($value !== ''):
                $builder->where('created_at ' . $operator, $value);
            endif;
        endforeach;

        $total = (int) $builder->countAllResults(false);
        $page = min($page, max(1, (int) ceil($total / $perPage)));
        $builder->select('id, name, email, status, created_at, read_at, archived_at')
            ->orderBy($column, $order);
        if ($column !== 'id'):
            $builder->orderBy('id', $order);
        endif;
        $rows = $builder->get($perPage, ($page - 1) * $perPage)
            ->getResultArray();

        return ['records' => $rows, 'pagination' => ['page' => $page, 'limit' => $perPage, 'totalRows' => $total]];
    }

    /**
     * Counts messages in each inbox status.
     *
     * @return array{unread: int, read: int, archived: int} Counts including empty states.
     */
    public function getStatusCounts(): array
    {
        $counts = ['unread' => 0, 'read' => 0, 'archived' => 0];
        $rows = $this->db->table('contact_messages')->select('status')->selectCount('id', 'total')
            ->groupBy('status')->get()->getResultArray();

        foreach ($rows as $row):
            if (isset($counts[$row['status']])):
                $counts[$row['status']] = (int) $row['total'];
            endif;
        endforeach;

        return $counts;
    }

    /**
     * Retrieves a message without changing its read status.
     *
     * @param string $id Message ID from the administrative route.
     * @return array|null Stored message, if found.
     */
    public function getMessage(string $id): ?array
    {
        return $this->db->table('contact_messages')->where('id', $id)->get()->getRowArray();
    }

    /**
     * Records the latest read or archive action atomically with the status change.
     * The previous status must still match the one shown to the administrator.
     *
     * @param string $id Message ID from the administrative route.
     * @param string $previousStatus Status shown in the detail view.
     * @param string $status Requested status.
     * @return bool Whether the change was applied.
     */
    public function changeStatus(string $id, string $previousStatus, string $status): bool
    {
        $builder = $this->db->table('contact_messages')
            ->where('id', $id)
            ->where('status', $previousStatus)
            ->set('status', $status);

        if ($status === 'read'):
            $builder->set('read_at', 'current_timestamp', false);
        elseif ($status === 'archived'):
            $builder->set('archived_at', 'current_timestamp', false);
        endif;

        return $builder->update() && $this->db->affectedRows() === 1;
    }
}
