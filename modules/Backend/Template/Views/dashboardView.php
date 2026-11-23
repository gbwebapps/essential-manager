<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW); ?>

<?= $this->section('beforeSectionView'); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::OVERVIEW_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Template\Config\Views::DASHBOARD_STYLE_PARTIAL); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
    <div id="manager-dashboard" class="manager-desk <?= $dashboardAccess['activities'] && $dashboardAccess['studio'] ? 'manager-desk-split' : ''; ?>">
        <?php if ($dashboardAccess['activities']): ?>
            <div id="manager-dashboard-activities" class="manager-desk-activities">
                <div id="manager-dashboard-overview"><?= $this->include(\Modules\Backend\Tasks\Config\Views::OVERVIEW_PARTIAL); ?></div>
                <div class="manager-postits" data-dashboard-tasks>
                    <?php foreach ($dashboardTasks as $task): ?>
                        <?= view(\Modules\Backend\Template\Config\Views::DASHBOARD_TASK_PARTIAL, ['task' => $task, 'dashboardPresenter' => $dashboardPresenter, 'dashboardAccess' => $dashboardAccess], ['saveData' => false]); ?>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($dashboardTasks)): ?><p class="manager-desk-empty"><?= ui_text('backend/managerDashboard.noActivityTasks', [], false); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($dashboardAccess['studio']): ?>
            <?= $this->include(\Modules\Backend\Template\Config\Views::DASHBOARD_STUDIO_PARTIAL); ?>
        <?php endif; ?>
        <?php if ( ! $dashboardAccess['activities'] && ! $dashboardAccess['studio']): ?><p class="manager-desk-empty"><?= ui_text('backend/managerDashboard.noSections', [], false); ?></p><?php endif; ?>
    </div>
<?= $this->endSection(); ?>
