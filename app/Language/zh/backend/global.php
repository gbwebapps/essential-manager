<?php declare(strict_types = 1);

return [
    'interface' => [
        'showPassword' => '显示密码',
        'hidePassword' => '隐藏密码',
        'themes' => '主题',
        'changeError' => '无法更改偏好设置，请重试。',
    ],
    'menu' => [
        'topRight' => [
            'admins' => '管理员',
            'groups' => '群组',
            'audits' => '活动日志', 
            'tokens' => '令牌', 
            'logs' => '登录历史', 
            'account' => '个人资料',
            'logout' => '退出登录'
        ],
        'bottomLeft' => [
            'dashboard' => '控制面板',
        ],
        'bottomRight' => [
            'settings' => '设置',
            'tools' => '工具', 
        ],
    ],
    'menuCounts' => [
        'active' => '已启用',
        'trashed' => '已删除',
        'suspended' => '已停用',
    ],
    'links' => [
        'selectAll' => '全选'
    ], 
    'labels' => [
        'select' => '选择'
    ], 
    'formats' => [
        'conversationalDate' => "yyyy'年'M'月'd'日' EEEE HH:mm:ss",
    ], 
    'buttons' => [
        'modules' => '模块',
        'services' => '服务',
        'backToTop' => '返回顶部', 
        'expandScreen' => '全屏显示',
        'reduceScreen' => '退出全屏',
        'options' => '选项', 
        'undo' => '撤销', 
        'export' => '导出', 
        'yes' => '是', 
        'no' => '否', 
        'close' => '关闭', 
        'exportPdf' => '导出 PDF', 
        'remove' => '移除', 
    ],
    'errors' => [
        'err403' => '访问被拒绝。您无权执行此操作。',
        'err404' => '请求的资源未找到或已被移除。',
        'err500' => '处理请求时发生严重错误。',
        'err504' => '服务器未及时响应。重试前请检查操作状态。',
        'errNetwork' => '无法与服务器通信。请检查连接或稍后重试。',
        'errResponse' => '服务器返回了无效响应。操作可能已经完成。',
        'errClient' => '界面发生错误。重试前请检查操作状态。',
        'errRequest' => '无法发起请求。',
        'errCsrf' => '安全验证失败。请刷新页面后重试。',
        'errHttp' => '服务器无法完成请求。',
        'fullscreenError' => '无法更改全屏模式。',
    ], 
    'modals' => [
        'exportPdfTitle' => 'PDF 导出设置', 
        'exportPdfOrientation' => '方向', 
        'exportPdfOrientationVert' => '纵向', 
        'exportPdfOrientationHoriz' => '横向', 
        'exportPdfFormat' => '格式', 
        'exportPdfMargin' => '页边距', 
        'exportPdfMarginSup' => '上边距', 
        'exportPdfMarginRgt' => '右边距', 
        'exportPdfMarginInf' => '下边距', 
        'exportPdfMarginLft' => '左边距', 
        'exportPdfCompression' => '压缩', 
        'exportPdfImgQuality' => '图片质量', 
        'globalTitle' => '确认请求'
    ], 
    'pagination' => [
        'messageLeft' => '第 %d 页，共 %d 页',
        'messageRight' => '显示第 %d 到 %d 条，共 %d 条',
        'first' => '首页',
        'last' => '尾页',
        'next' => '下一页',
        'previous' => '上一页',
    ],
    'audits' => [
        'importData' => '将数据导入表 %s。已插入 %d 条记录，已更新 %d 条记录。',
    ],
    'messages' => [
        'confirmSensitiveAction' => '请使用您的账户密码确认此操作。',
        'currentPasswordInfo' => '输入您的账户密码以确认此操作。',
        'getDataError' => '获取列表操作时出错。',
        'getUUIDError' => '获取详情操作时出错。', 
        'UUIDNotFound' => '未找到记录。', 
        'permissionDenied' => '拒绝访问。',
        'unsavedChanges' => '存在未保存的更改。是否放弃更改并离开？'
    ]
];
