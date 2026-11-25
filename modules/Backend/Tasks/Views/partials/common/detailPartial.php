<?php
    $isDraftBody = $taskBodyDraft ?? false;
    $showBodyNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_show') || ($isDraftBody && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add'));
    $canWorkSteps = ! $isDraftBody && ($ownerStepActions ?? false) && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_complete');
    $groups = ['task' => $showBodyNotes ? ($task->notes ?? []) : []];
?>
<div class="owner-task-body">
    <?php if (trim((string) ($task->description ?? '')) !== ''): ?>
        <p class="owner-task-description"><?= esc($task->description); ?></p>
    <?php endif; ?>
    <?php if ($groups['task'] !== []): ?>
        <div class="owner-task-notes" aria-label="<?= esc(lang('backend/tasks.panels.taskNotes'), 'attr'); ?>">
            <?php foreach ($groups['task'] as $noteIndex=>$note): ?>
                <?= view('Modules\Backend\Tasks\Views\partials\common\bodyNotePartial', ['note'=>$note, 'noteKey'=>'task-note-'.$noteIndex], ['saveData'=>false]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (($task->steps ?? []) !== []): ?>
        <ol class="owner-task-steps" aria-label="<?= esc(lang('backend/tasks.labels.steps'), 'attr'); ?>">
            <?php foreach ($task->steps as $position=>$step): $stepCheckboxId='owner-step-'.($step->uuid ?? ''); ?>
                <li class="owner-task-step" data-owner-step="<?= esc($step->uuid ?? '', 'attr'); ?>">
                    <div class="owner-step-row">
                        <span class="task-step-number" aria-hidden="true"><?= $position+1; ?></span>
                        <?php if ($canWorkSteps): ?>
                            <form novalidate class="m-0 p-0" data-owner-step-form>
                                <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                                <input type="hidden" name="step_uuid" value="<?= esc($step->uuid, 'attr'); ?>">
                                <input type="checkbox" id="<?= esc($stepCheckboxId, 'attr'); ?>" class="form-check-input" name="completed" value="1" data-owner-step-completion <?= $step->completed ? 'checked' : ''; ?>>
                            </form>
                            <label for="<?= esc($stepCheckboxId, 'attr'); ?>" class="owner-step-label <?= $step->completed ? 'app-text-muted text-decoration-line-through' : ''; ?>"><?= esc($step->title); ?></label>
                        <?php else: ?>
                            <i class="fa-<?= ($step->completed ?? false) ? 'solid fa-square-check app-text-success' : 'regular fa-square'; ?>" aria-hidden="true"></i>
                            <span class="text-break"><?= esc($step->title); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($showBodyNotes && ($step->notes ?? []) !== []): ?>
                        <div class="owner-step-notes">
                            <?php foreach ($step->notes as $noteIndex=>$note): ?>
                                <?= view('Modules\Backend\Tasks\Views\partials\common\bodyNotePartial', ['note'=>$note, 'noteKey'=>'step-'.$position.'-note-'.$noteIndex], ['saveData'=>false]); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</div>
