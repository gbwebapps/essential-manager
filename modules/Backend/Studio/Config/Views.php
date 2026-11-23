<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Config;

use CodeIgniter\Config\BaseConfig;

/** Percorsi delle viste del solo modulo Studio. */
class Views extends BaseConfig
{
    public const ADD_VIEW = 'Modules\\Backend\\Studio\\Views\\tasks\\addView';
    public const EDIT_VIEW = 'Modules\\Backend\\Studio\\Views\\tasks\\editView';
    public const SHOW_VIEW = 'Modules\\Backend\\Studio\\Views\\tasks\\showView';
    public const INDEX_VIEW = 'Modules\\Backend\\Studio\\Views\\indexView';
    public const LIST_PARTIAL = 'Modules\\Backend\\Studio\\Views\\partials\\listPartial';
    public const TASK_PARTIAL = 'Modules\\Backend\\Studio\\Views\\partials\\taskPartial';
    public const TASK_BODY_PARTIAL = 'Modules\\Backend\\Studio\\Views\\partials\\taskBodyPartial';
    public const NOTE_PARTIAL = 'Modules\\Backend\\Studio\\Views\\partials\\notePartial';
    public const STYLE_PARTIAL = 'Modules\\Backend\\Studio\\Views\\partials\\stylePartial';
    public const MODALS_PARTIAL = 'Modules\\Backend\\Studio\\Views\\partials\\modalsPartial';
}
