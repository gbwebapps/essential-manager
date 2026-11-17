<?= view('backend/components/import/sheetsPreviewPartial', [
    'importId' => $importId ?? '',
    'plan' => $plan,
    'sheets' => $sheets,
    'syncNoChanges' => lang('backend/admintransfer.messages.syncNoChanges'),
    'reExportKey' => 'backend/admintransfer.descriptions.reExport',
    'passwordFieldId' => 'admintransfer-import-current-password',
]); ?>
