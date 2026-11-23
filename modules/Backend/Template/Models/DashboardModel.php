<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Models;

use App\Models\Backend\BackendModel;

/** Letture della Dashboard Manager, limitate ai contesti stabiliti dal server. */
class DashboardModel extends BackendModel
{
    public const PREVIEW_SIZE = 4;
    public const STUDIO_PREVIEW_SIZE = 3;

    /**
     * Legge gli ultimi quattro task e il riepilogo globale nella stessa fotografia transazionale.
     * @param string $adminUuid Identità autenticata per il task corrente personale.
     * @param bool $projects Consente task di progetti attivi.
     * @param bool $applications Consente task di applicazioni e progetti padre attivi.
     * @param bool $notes Consente i conteggi delle note, senza leggerne i testi.
     * @return array Anteprima limitata, riepilogo completo e corrente indipendente dalla data di creazione.
     */
    public function activitySnapshot(string $adminUuid, bool $projects, bool $applications, bool $notes): array
    {
        $scope = $this->scope(false, $projects, $applications);
        $this->db->transBegin();
        try {
            $summary = array_map('intval', $this->db->query("select count(*) total,
                coalesce(sum(tasks.status <> 'completed'), 0) open,
                coalesce(sum(tasks.status = 'in_progress'), 0) in_progress,
                coalesce(sum(progress.remaining_steps), 0) remaining_steps
                {$this->contextFrom()}
                left join (select task_uuid, sum(completed_at is null) remaining_steps from task_steps group by task_uuid) progress on progress.task_uuid = tasks.uuid
                where tasks.deleted_at is null and {$scope}")->getRowArray());
            $records = $this->readTasks($adminUuid, false, $projects, $applications, $notes, self::PREVIEW_SIZE);
            $current = $this->readTasks($adminUuid, false, $projects, $applications, $notes, 1, true)[0] ?? null;
            if ($current): $current->next_step = $current->next_step_title !== null ? (object) ['title' => $current->next_step_title] : null; endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Dashboard snapshot failed.'); endif;
            $this->db->transCommit();
            return ['records' => $records, 'current' => $current, 'overview' => $summary];
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    /** @param string $adminUuid Identità server. @param bool $notes Accesso ai conteggi note. @return array<object> Al massimo tre task Studio: corrente personale per primo, poi i più recenti; studio_total conserva il conteggio completo. */
    public function studioTasks(string $adminUuid, bool $notes): array
    {
        return $this->readTasks($adminUuid, true, false, false, $notes, self::STUDIO_PREVIEW_SIZE, false, true);
    }

    /** @return int Numero delle sole note autonome attive dello Studio, dopo il controllo dei permessi. */
    public function studioNoteCount(): int
    {
        return $this->db->table('notes')->where('is_studio', 1)->where('deleted_at', null)->countAllResults();
    }

    /** @return string Join statiche dei proprietari, condivise da conteggio, corrente e pagina. */
    private function contextFrom(): string
    {
        return 'from tasks left join projects direct on direct.uuid = tasks.project_uuid
            left join applications app on app.uuid = tasks.application_uuid
            left join projects origin on origin.uuid = app.project_uuid';
    }

    /** @param bool $studio Contesto interno. @param bool $projects Accesso progetti. @param bool $applications Accesso applicazioni. @return string Filtro statico; nessuna colonna o condizione arriva dal browser. */
    private function scope(bool $studio, bool $projects, bool $applications): string
    {
        if ($studio): return 'tasks.is_studio = 1'; endif;
        $owners = [];
        if ($projects): $owners[] = '(direct.uuid is not null and direct.deleted_at is null)'; endif;
        if ($applications): $owners[] = '(app.uuid is not null and app.deleted_at is null and origin.uuid is not null and origin.deleted_at is null)'; endif;
        return $owners ? 'tasks.is_studio = 0 and (' . implode(' or ', $owners) . ')' : '1 = 0';
    }

    /**
     * Proiezione comune dei task: aggrega passi e note senza query per ogni scheda.
     * @param string $adminUuid Identità server. @param bool $studio Contesto interno.
     * @param bool $projects Accesso progetti. @param bool $applications Accesso applicazioni.
     * @param bool $notes Accesso conteggi note. @param int $limit Limite interno obbligatorio.
     * @param bool $currentOnly Seleziona soltanto il corrente.
     * @param bool $currentFirst Ordina il corrente prima degli altri task, senza duplicarlo.
     * @return array<object> Record autorizzati con stato, avanzamento e prossimo passo.
     */
    private function readTasks(string $adminUuid, bool $studio, bool $projects, bool $applications, bool $notes, int $limit, bool $currentOnly = false, bool $currentFirst = false): array
    {
        $scope = $this->scope($studio, $projects, $applications);
        $studioTotal = $studio ? '(select count(*) from tasks studio_tasks where studio_tasks.is_studio = 1 and studio_tasks.deleted_at is null)' : '0';
        $noteSelect = $notes ? 'coalesce(note_totals.note_count, 0)' : '0';
        $noteJoin = $notes ? 'left join (
            select owner_uuid, count(*) note_count from (
                select task_uuid owner_uuid from notes where task_uuid is not null and deleted_at is null
                union all
                select steps.task_uuid owner_uuid from notes join task_steps steps on steps.uuid = notes.step_uuid where notes.deleted_at is null
            ) visible_notes group by owner_uuid
        ) note_totals on note_totals.owner_uuid = tasks.uuid' : '';
        $sql = "select tasks.uuid, tasks.title, tasks.description, tasks.status, tasks.created_at, tasks.is_studio,
            tasks.project_uuid, tasks.application_uuid, coalesce(direct.name, origin.name) project_name,
            app.name application_name, coalesce(progress.step_count, 0) step_count,
            coalesce(progress.completed_count, 0) completed_count, next_step.title next_step_title,
            {$noteSelect} note_count, {$studioTotal} studio_total, (work.task_uuid is not null and tasks.status <> 'completed') is_current
            {$this->contextFrom()}
            left join (
                select task_uuid, count(*) step_count, sum(completed_at is not null) completed_count,
                    min(case when completed_at is null then position end) next_position
                from task_steps group by task_uuid
            ) progress on progress.task_uuid = tasks.uuid
            left join task_steps next_step on next_step.task_uuid = tasks.uuid and next_step.position = progress.next_position
            {$noteJoin}
            left join admin_work_state work on work.task_uuid = tasks.uuid and work.admin_uuid = ? and work.work_context = ?
            where tasks.deleted_at is null and {$scope}";
        if ($currentOnly): $sql .= " and work.task_uuid is not null and tasks.status <> 'completed'"; endif;
        $sql .= $currentFirst ? ' order by is_current desc, tasks.created_at desc, tasks.id desc' : ' order by tasks.created_at desc, tasks.id desc';
        $sql .= ' limit ?';
        $params = [$adminUuid, $studio ? 'studio' : 'activities', $limit];
        return $this->db->query($sql, $params)->getResult();
    }
}
