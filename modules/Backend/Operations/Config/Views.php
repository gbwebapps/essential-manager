<?php declare(strict_types = 1);

namespace Modules\Backend\Operations\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Definisce i percorsi namespaced delle viste del modulo Operations.
 */
class Views extends BaseConfig
{
    public const INDEX_VIEW = 'Modules\\Backend\\Operations\\Views\\indexView';
    public const SHOW_ALL_VIEW = 'Modules\\Backend\\Operations\\Views\\showAllView';
    public const ADD_VIEW = 'Modules\\Backend\\Operations\\Views\\addView';
    public const EDIT_VIEW = 'Modules\\Backend\\Operations\\Views\\editView';
    public const SHOW_VIEW = 'Modules\\Backend\\Operations\\Views\\showView';

    public const INDEX_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\index\\indexPartial';
    public const SHOW_ALL_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\showAll\\showAllPartial';
    public const ADD_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\add\\addPartial';
    public const EDIT_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\edit\\editPartial';
    public const EDIT_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\edit\\generalDataPartial';
    public const SHOW_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\show\\showPartial';
    public const SHOW_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\show\\generalDataPartial';
    public const CHANGE_STATUS_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\show\\changeStatusPartial';
    public const META_DATA_PARTIAL = 'Modules\\Backend\\Operations\\Views\\partials\\common\\metaDataPartial';
}
