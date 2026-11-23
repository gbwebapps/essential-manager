<?php if ($listing['records']): ?>
<?php $pager=['data'=>['pagination'=>$listing['pagination']]]; ?>
<div class="studio-pagination studio-pagination-top" data-studio-pagination><?= view('backend/template/paginationView',$pager,['saveData'=>false]); ?></div>
<div class="studio-records" data-studio-<?= $kind==='tasks'?'task':'note'; ?>-list>
    <?php foreach ($listing['records'] as $record): ?>
        <?= view($kind==='tasks'?\Modules\Backend\Studio\Config\Views::TASK_PARTIAL:\Modules\Backend\Studio\Config\Views::NOTE_PARTIAL,
            ['studioTask'=>$kind==='tasks'?$record:null,'studioNote'=>$kind==='notes'?$record:null,'openedTasks'=>$openedTasks,
            'currentAdmin'=>$currentAdmin,'currentTask'=>$currentTask,'canReadNotes'=>$canReadNotes,'canReadTaskDetails'=>$canReadTaskDetails,
            'canEditTasks'=>$canEditTasks,'canDeleteTasks'=>$canDeleteTasks,'canEditNotes'=>$canEditNotes,'canDeleteNotes'=>$canDeleteNotes],['saveData'=>false]); ?>
    <?php endforeach; ?>
</div>

<div class="studio-pagination studio-pagination-bottom" data-studio-pagination><?= view('backend/template/paginationView',$pager,['saveData'=>false]); ?></div>
<?php else: ?>
    <p class="studio-empty text-center fw-bold app-text-muted"><?= ui_text('backend/studio.'.($kind==='tasks'?'noTasksFound':'noNotesFound'),[],false); ?></p>
<?php endif; ?>
