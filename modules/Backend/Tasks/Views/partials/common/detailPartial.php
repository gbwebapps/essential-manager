<?php if ( ! empty($task->description)): ?>
    <p class="project-related-text"><?= esc($task->description); ?></p>
<?php endif; ?>
<?php if ( ! empty($task->steps)): ?>
    <ul class="project-related-steps" aria-label="<?= esc(lang('backend/tasks.labels.steps'), 'attr'); ?>">
        <?php foreach ($task->steps as $taskStep): ?>
            <li><i class="<?= $taskStep->completed ? 'fa-solid fa-square-check' : 'fa-regular fa-square'; ?>" aria-hidden="true"></i><span><span class="visually-hidden"><?= ui_text('backend/tasks.labels.' . ($taskStep->completed ? 'stepCompleted' : 'stepPending'), [], false); ?>: </span><?= esc($taskStep->title); ?></span></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
