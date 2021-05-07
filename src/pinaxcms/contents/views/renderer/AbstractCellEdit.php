<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinaxcms_contents_views_renderer_AbstractCellEdit extends pinax_components_render_RenderCell
{
    protected $canView = true;
    protected $canEdit = true;
    protected $canEditDraft = true;
    protected $canDelete = true;
    protected $isDefaultLanguage;

    function __construct($application)
	{
        parent::__construct($application);

        $this->isDefaultLanguage = __Session::get('pinax.editingLanguageIsDefault');
    }


    protected function loadAcl($key)
    {
        $pageId = $this->application->getPageId($key);
        $this->canView = $this->user->acl($pageId, 'visible', null, $key);
        $this->canEdit = $this->canView && $this->user->acl($pageId, 'edit', null, $key);
        $this->canEditDraft = $this->canView && $this->user->acl($pageId, 'editDraft', null, $key);
        $this->canDelete = $this->canEdit && $this->user->acl($pageId, 'delete', null, $key);
    }

    protected function renderEditButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('pinax.datagrid.action.editCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('PNX_RECORD_EDIT'),
                    'id' => $key,
                    'action' => 'edit'
                )
            );
        }

        return $output;
    }

    protected function renderEditDraftButton($key, $row, $enabled = true)
    {
        if ($this->canEdit) {
            $output = '';
        } else if ($this->canEditDraft) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('pinax.datagrid.action.editDraftCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('PNX_RECORD_EDIT_DRAFT'),
                    'id' => $key,
                    'action' => 'editDraft'
                )
            );
        }

        return $output;
    }

    protected function renderDeleteButton($key, $row)
	{
        $output = '';
        if ($this->canView && $this->canDelete &&
            ($this->notDocumentOrDocumentWithTranslation($row) || $this->isDefaultLanguage)) {
            $output .= __Link::makeLinkWithIcon( 'actionsMVC',
                                                            __Config::get('pinax.datagrid.action.deleteCssClass'),
                                                            [
                                                                'title' => __T('PNX_RECORD_DELETE'),
                                                                'id' => $key,
                                                                'action' => 'delete'
                                                            ],
                                                            __T('PNX_RECORD_MSG_DELETE'),
                                                            [
                                                                'model' => property_exists($row, 'model') ? $row->model : $row->getClassName(false)
                                                            ]
                                                    );
        }

		return $output;
	}

    protected function renderDeleteLanguageButton($key, $row)
	{
        $output = '';
        if ($this->canView && $this->canDelete && $this->notDocumentOrDocumentWithTranslation($row)) {
            $output .= __Link::makeLinkWithIcon( 'actionsMVC',
                                                            'icon-cut btn-icon',
                                                            [
                                                                'title' => __T('PNX_RECORD_DELETE_VERSION'),
                                                                'id' => $key,
                                                                'action' => 'deletelanguage'
                                                            ],
                                                            __T('PNX_RECORD_MSG_DELETE_LANGUAGE'),
                                                            [
                                                                'model' => property_exists($row, 'model') ? $row->model : $row->getClassName(false)
                                                            ]
                                                        );
        }

		return $output;
	}



    protected function renderVisibilityButton($key, $row)
    {
        $output = '';
        if ($this->canView && $this->canEdit && $this->notDocumentOrDocumentWithTranslation($row)) {
            $output .= __Link::makeLinkWithIcon( 'actionsMVC',
                                                           __Config::get($row->isVisible() ? 'pinax.datagrid.action.showCssClass' : 'pinax.datagrid.action.hideCssClass'),
                                                           array(
                                                                'title' => $row->isVisible() ? __T('Hide') : __T('Show'),
                                                                'id' => $key,
                                                                'action' => 'togglevisibility' ),
                                                                null,
                                                                [
                                                                    'model' => property_exists($row, 'model') ? $row->model : $row->getClassName(false)
                                                                ]
                                                            );
        }

        return $output;
    }

    protected function renderCheckBox($key, $row)
	{
        $output = '';
        if ($this->canView && $this->canDelete) {
            $output .= '<input name="check[]" data-id="'.$row->getId().'" type="checkbox">';
        }

		return $output;
    }

    /**
     * @param object $row
     * @return boolean
     */
    protected function notDocumentOrDocumentWithTranslation($row)
    {
        return !($row instanceof pinax_dataAccessDoctrine_ActiveRecordDocument) ||
                ($row instanceof pinax_dataAccessDoctrine_ActiveRecordDocument && $row->isTranslated());
    }
}

