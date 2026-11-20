<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Config;

use CodeIgniter\Config\BaseConfig;

/** Viste condivise esclusivamente dai moduli applicativi di Manager. */
class Views extends BaseConfig
{
    public const MAIN_VIEW = 'Modules\\Backend\\Template\\Views\\mainView';
    public const NAVIGATION_PARTIAL = 'Modules\\Backend\\Template\\Views\\partials\\navigationPartial';
}
