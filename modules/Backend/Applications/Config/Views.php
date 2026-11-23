<?php declare(strict_types = 1);

namespace Modules\Backend\Applications\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Definisce i percorsi namespaced delle viste del modulo Applications.
 */
class Views extends BaseConfig
{
    public const SHOW_ALL_VIEW = 'Modules\\Backend\\Applications\\Views\\showAllView';
    public const ADD_VIEW = 'Modules\\Backend\\Applications\\Views\\addView';
    public const EDIT_VIEW = 'Modules\\Backend\\Applications\\Views\\editView';
    public const SHOW_VIEW = 'Modules\\Backend\\Applications\\Views\\showView';

    public const SHOW_ALL_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\showAll\\showAllPartial';
    public const ADD_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\add\\addPartial';
    public const EDIT_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\edit\\editPartial';
    public const EDIT_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\edit\\generalDataPartial';
    public const SHOW_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\show\\showPartial';
    public const SHOW_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\show\\generalDataPartial';
    public const PROJECT_ACTIONS_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\applicationActionsPartial';
    public const DRAFT_PANELS_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\draftPanelsPartial';
    public const SECTION_STYLE_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\sectionStylePartial';
    public const RELATED_PANELS_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\relatedPanelsPartial';
    public const RELATED_ACTIONS_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\relatedActionsPartial';
    public const RELATED_ITEMS_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\relatedItemsPartial';
    public const META_DATA_PARTIAL = 'Modules\\Backend\\Applications\\Views\\partials\\common\\metaDataPartial';
}
