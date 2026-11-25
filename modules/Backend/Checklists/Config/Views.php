<?php declare(strict_types = 1);

namespace Modules\Backend\Checklists\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Definisce i percorsi namespaced delle viste del modulo Checklists.
 */
class Views extends BaseConfig
{
    public const SHOW_ALL_VIEW = 'Modules\\Backend\\Checklists\\Views\\showAllView';
    public const ADD_VIEW = 'Modules\\Backend\\Checklists\\Views\\addView';
    public const EDIT_VIEW = 'Modules\\Backend\\Checklists\\Views\\editView';
    public const SHOW_VIEW = 'Modules\\Backend\\Checklists\\Views\\showView';

    public const SHOW_ALL_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\showAll\\showAllPartial';
    public const ADD_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\add\\addPartial';
    public const EDIT_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\edit\\editPartial';
    public const EDIT_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\edit\\generalDataPartial';
    public const SHOW_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\show\\showPartial';
    public const SHOW_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\show\\generalDataPartial';
    public const FORM_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\common\\formPartial';
    public const STEP_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\common\\stepPartial';
    public const META_DATA_PARTIAL = 'Modules\\Backend\\Checklists\\Views\\partials\\common\\metaDataPartial';
}
