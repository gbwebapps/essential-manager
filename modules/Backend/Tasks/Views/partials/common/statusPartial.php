<?php $taskStatus = ['value'=>$task->status, 'label'=>'backend/tasks.statuses.' . $task->status]; ?>
<span class="badge task-status" data-task-status="<?= esc($taskStatus['value'], 'attr'); ?>"><?= ui_text($taskStatus['label'], [], false); ?></span>
