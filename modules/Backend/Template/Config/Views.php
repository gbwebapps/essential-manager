<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Config;

use CodeIgniter\Config\BaseConfig;

/** Viste condivise esclusivamente dai moduli applicativi di Manager. */
class Views extends BaseConfig
{
    public const DASHBOARD_VIEW = 'Modules\\Backend\\Template\\Views\\dashboardView';
    public const DASHBOARD_STYLE_PARTIAL = 'Modules\\Backend\\Template\\Views\\partials\\dashboard\\stylePartial';
    public const DASHBOARD_TASK_PARTIAL = 'Modules\\Backend\\Template\\Views\\partials\\dashboard\\taskPartial';
    public const DASHBOARD_STUDIO_PARTIAL = 'Modules\\Backend\\Template\\Views\\partials\\dashboard\\studioPartial';
    public const MAIN_VIEW = 'Modules\\Backend\\Template\\Views\\mainView';
    public const MODAL_STYLE_PARTIAL = 'Modules\\Backend\\Template\\Views\\partials\\modalStylePartial';
    public const NAVIGATION_PARTIAL = 'Modules\\Backend\\Template\\Views\\partials\\navigationPartial';
}
