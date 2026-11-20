<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Config;

use CodeIgniter\Config\BaseConfig;

/** Percorsi delle viste CRUD e dei partial condivisi del modulo Tasks. */
class Views extends BaseConfig
{
    public const SHOW_ALL_VIEW = 'Modules\\Backend\\Tasks\\Views\\showAllView';
    public const ADD_VIEW = 'Modules\\Backend\\Tasks\\Views\\addView';
    public const EDIT_VIEW = 'Modules\\Backend\\Tasks\\Views\\editView';
    public const SHOW_VIEW = 'Modules\\Backend\\Tasks\\Views\\showView';
    public const SHOW_ALL_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\showAll\\showAllPartial';
    public const ADD_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\add\\addPartial';
    public const EDIT_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\edit\\editPartial';
    public const EDIT_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\edit\\generalDataPartial';
    public const SHOW_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\show\\showPartial';
    public const SHOW_GENERAL_DATA_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\show\\generalDataPartial';
    public const META_DATA_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\metaDataPartial';
    public const FORM_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\taskFormPartial';
    public const EDITOR_STEP_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\editorStepPartial';
    public const STEPS_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\stepsPartial';
    public const NOTES_PANEL_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\notesPanelPartial';
    public const NOTES_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\noteItemsPartial';
    public const NOTE_MODAL_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\noteModalPartial';
    public const STEP_FIELD_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\stepFieldPartial';
    public const FORM_FIELDS_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\formFieldsPartial';
    public const DETAIL_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\detailPartial';
    public const OVERVIEW_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\overviewPartial';
    public const ACTION_LINKS_STYLE_PARTIAL = 'Modules\\Backend\\Tasks\\Views\\partials\\common\\actionLinksStylePartial';
}
