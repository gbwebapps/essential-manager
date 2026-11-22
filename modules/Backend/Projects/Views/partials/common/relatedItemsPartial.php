<?php if (empty($relatedItems)): ?>
    <p class="small app-text-muted px-3 py-2 mb-0"><?= ui_text(empty($relatedProject->uuid) ? 'backend/projects.related.saveProjectFirst' : 'backend/projects.related.no' . ($relatedKind === 'task' ? 'Tasks' : 'Notes'), [], false); ?></p>
<?php else: ?>
    <?php foreach ($relatedItems as $relatedItem): ?>
        <?php
            $collapseId = 'project-' . $relatedKind . '-' . $relatedItem->uuid;
            $isTask = $relatedKind === 'task';
            $summary = $isTask ? $relatedItem->title : strtok($relatedItem->content, "\r\n");
            $record = $isTask ? ['uuid' => $relatedItem->uuid, 'title' => $relatedItem->title, 'description' => $relatedItem->description, 'status' => $relatedItem->status, 'steps' => $relatedItem->steps] : ['uuid' => $relatedItem->uuid, 'content' => $relatedItem->content];
            $completed = $isTask ? count(array_filter($relatedItem->steps, static fn (object $step): bool => $step->completed)) : 0;
            $relatedActions = view(\Modules\Backend\Projects\Config\Views::RELATED_ACTIONS_PARTIAL, [
                'relatedKind' => $relatedKind, 'isTask' => $isTask, 'record' => $record,
                'relatedReadOnly' => $relatedReadOnly ?? false, 'relatedItem' => $relatedItem,
                'relatedProject' => $relatedProject, 'currentAdmin' => $currentAdmin ?? null,
            ], ['saveData' => false]);
        ?>
        <article class="project-related-item">
            <div class="project-related-header d-flex align-items-start gap-1">
            <button type="button" id="<?= esc($collapseId, 'attr'); ?>-toggle" class="project-related-toggle" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId, 'attr'); ?>" aria-controls="<?= esc($collapseId, 'attr'); ?>" aria-expanded="false">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                <span class="project-related-summary">
                    <span class="project-related-title fw-bold"><?= esc($summary); ?></span>
                    <?php if ($isTask): ?>
                        <span class="project-related-meta app-text-muted">
                            <span class="badge app-bg-muted-subtle app-text-muted-emphasis border"><?= ui_text('backend/tasks.statuses.' . $relatedItem->status, [], false); ?></span>
                            <span><?= ui_text('backend/tasks.labels.stepsProgress', [$completed, count($relatedItem->steps)], false); ?></span>
                        </span>
                    <?php endif; ?>
                </span>
            </button>
                <?php if (trim($relatedActions) !== ''): ?>
                    <div class="d-flex flex-row flex-nowrap gap-1 flex-shrink-0 pt-2 pe-2"><?= $relatedActions; ?></div>
                <?php endif; ?>
            </div>
            <div id="<?= esc($collapseId, 'attr'); ?>" class="collapse" role="region" aria-labelledby="<?= esc($collapseId, 'attr'); ?>-toggle">
                <div class="project-related-detail">
                    <?php if ($isTask): ?>
                        <?= view(\Modules\Backend\Tasks\Config\Views::DETAIL_PARTIAL, ['task' => $relatedItem]); ?>
                    <?php else: ?>
                        <?= view(\Modules\Backend\Notes\Config\Views::DETAIL_PARTIAL, ['note' => $relatedItem]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
