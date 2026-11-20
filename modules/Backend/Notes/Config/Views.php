<?php declare(strict_types = 1);

namespace Modules\Backend\Notes\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Definisce i percorsi namespaced delle viste del modulo Notes.
 */
class Views extends BaseConfig
{
    public const INDEX_VIEW = 'Modules\\Backend\\Notes\\Views\\indexView';
    public const SHOW_ALL_VIEW = 'Modules\\Backend\\Notes\\Views\\showAllView';
    public const ADD_VIEW = 'Modules\\Backend\\Notes\\Views\\addView';
    public const EDIT_VIEW = 'Modules\\Backend\\Notes\\Views\\editView';
    public const SHOW_VIEW = 'Modules\\Backend\\Notes\\Views\\showView';

    public const INDEX_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\index\\indexPartial';
    public const SHOW_ALL_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\showAll\\showAllPartial';
    public const ADD_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\add\\addPartial';
    public const EDIT_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\edit\\editPartial';
    public const EDIT_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\edit\\generalDataPartial';
    public const SHOW_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\show\\showPartial';
    public const SHOW_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\show\\generalDataPartial';
    public const CHANGE_STATUS_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\show\\changeStatusPartial';
    public const FORM_FIELDS_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\common\\formFieldsPartial';
    public const DETAIL_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\common\\detailPartial';
    public const META_DATA_PARTIAL = 'Modules\\Backend\\Notes\\Views\\partials\\common\\metaDataPartial';
}
