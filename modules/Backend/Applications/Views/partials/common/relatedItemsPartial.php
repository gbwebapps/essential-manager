<?php if (empty($relatedItems)): ?>
    <p class="small app-text-muted px-3 py-2 mb-0"><?= ui_text(empty($relatedApplication->uuid) ? 'backend/applications.related.saveApplicationFirst' : 'backend/applications.related.no' . ($relatedKind === 'task' ? 'Tasks' : 'Notes'), [], false); ?></p>
<?php else: ?>
    <?php foreach ($relatedItems as $relatedItem): ?>
        <?php
            $collapseId = 'application-' . $relatedKind . '-' . $relatedItem->uuid;
            $isTask = $relatedKind === 'task';
            $summary = trim(preg_replace('/\s+/u', ' ', $isTask ? $relatedItem->title : $relatedItem->content));
            $record = $isTask ? ['uuid' => $relatedItem->uuid, 'title' => $relatedItem->title, 'description' => $relatedItem->description, 'status' => $relatedItem->status, 'steps' => $relatedItem->steps] : ['uuid' => $relatedItem->uuid, 'content' => $relatedItem->content];
            $completed = $isTask ? count(array_filter($relatedItem->steps, static fn (object $step): bool => $step->completed)) : 0;
            $relatedActions = view(\Modules\Backend\Applications\Config\Views::RELATED_ACTIONS_PARTIAL, [
                'relatedKind' => $relatedKind, 'isTask' => $isTask, 'record' => $record,
                'relatedReadOnly' => $relatedReadOnly ?? false, 'relatedItem' => $relatedItem,
                'relatedApplication' => $relatedApplication, 'currentAdmin' => $currentAdmin ?? null,
            ], ['saveData' => false]);
        ?>
        <article class="application-related-item task-related-item">
            <div class="application-related-header task-note-header d-flex align-items-center gap-1">
            <button type="button" id="<?= esc($collapseId, 'attr'); ?>-toggle" class="application-related-toggle task-related-toggle flex-grow-1" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId, 'attr'); ?>" aria-controls="<?= esc($collapseId, 'attr'); ?>" aria-expanded="false">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                <span class="application-related-summary task-related-summary">
                    <span class="application-related-title task-related-title fw-bold text-truncate"><?= esc(mb_strlen($summary) > 50 ? mb_substr($summary, 0, 49) . '…' : $summary); ?></span>
                    <?php if ($isTask): ?>
                        <span class="application-related-meta task-related-meta app-text-muted">
                            <span class="badge task-status" data-task-status="<?= esc($relatedItem->status, 'attr'); ?>"><?= ui_text('backend/tasks.statuses.' . $relatedItem->status, [], false); ?></span>
                            <?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $completed, 'totalSteps' => count($relatedItem->steps)], ['saveData' => false]); ?>
                        </span>
                    <?php endif; ?>
                </span>
            </button>
                <div class="task-note-actions d-flex flex-row flex-nowrap justify-content-end align-items-center gap-1 flex-shrink-0 pe-2">
                    <button type="button" id="<?= esc($collapseId, 'attr'); ?>-read" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link text-nowrap" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId, 'attr'); ?>" aria-controls="<?= esc($collapseId, 'attr'); ?>" aria-expanded="false"><?= ui_text('backend/tasks.actions.readNote', [], false); ?></button>
                    <?= $relatedActions; ?>
                </div>
            </div>
            <div id="<?= esc($collapseId, 'attr'); ?>" class="collapse" role="region" aria-labelledby="<?= esc($collapseId, 'attr'); ?>-toggle">
                <div class="application-related-detail task-related-detail">
                    <?php if ($isTask): ?>
                        <?php if (mb_strlen($summary) > 50): ?>
                            <p class="application-related-text fw-bold mb-2"><?= esc($relatedItem->title); ?></p>
                        <?php endif; ?>
                        <?= view(\Modules\Backend\Tasks\Config\Views::DETAIL_PARTIAL, ['task' => $relatedItem]); ?>
                    <?php else: ?>
                        <?= view(\Modules\Backend\Notes\Config\Views::DETAIL_PARTIAL, ['note' => $relatedItem]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
