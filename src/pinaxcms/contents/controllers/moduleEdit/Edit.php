<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_Edit extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    /**
     * @param int $id
     * @return void
     */
    public function execute($id)
    {
        $this->checkPermissionForBackend();

        $pageId = $this->application->getPageId();
        if (!$this->user->acl($pageId, 'visible', null, $id) || !$this->user->acl($pageId, 'edit', null, $id)) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $this->setViewContent($id);
    }

    protected function setViewContent($id, $status='PUBLISHED')
    {
        if (is_a($this->view, 'pinaxcms_views_components_FormEditWithDocumentStatus')) {
            $this->setViewContentWithStatusBar($id);
        } else {
            $this->setViewContentSimple($id, $status);
        }
    }

    protected function setViewContentSimple($id, $status='PUBLISHED')
    {
        $c = $this->view->getComponentById('__model');
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $data = $contentproxy->loadContent($id, $c->getAttribute('value'), $status);
        $data['__id'] = $id;
        $this->view->setData($data);
    }

    protected function setViewContentWithStatusBar($id)
    {
        $availableData = $this->loadContent($id);
        $hasPublishedVersion = $availableData[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] &&
                                    $availableData[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED]['document_id'] == $id;
        $hasDraftVersion = $availableData[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] &&
                                    $availableData[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT]['document_id'] == $id;

        if ($id && !$hasPublishedVersion && !$hasDraftVersion) {
            pinax_helpers_Navigation::notFound();
        }

        $pageId = $this->application->getPageId();
        $defaultState = $this->getEditStatus($id, $hasPublishedVersion, $hasDraftVersion);
        $statusToEdit = __Request::get('status', $defaultState);

        $this->view->setAvailableStatus($hasPublishedVersion, $hasDraftVersion);
        $this->view->setStatusToEdit($statusToEdit);
        $this->setButtonVisibility($statusToEdit,
                                        $this->user->acl($pageId, 'edit', null, $id),
                                        $this->user->acl($pageId, 'editDraft', null, $id) && __Config::get('pinaxcms.content.draft'));

        $data = $availableData[$statusToEdit];
        $data['__id'] = $id;
        $this->view->setData($data);
    }


    /**
     * @param integer $id
     * @return array
     */
    protected function loadContent($id)
    {
        $availableData = [
            pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED => null,
            pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT => null
        ];

        $c = $this->view->getComponentById('__model');
        $model = $c->getAttribute('value');
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $availableData[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] = $contentproxy->loadContent($id, $model, pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED);

        if (__Config::get('pinaxcms.content.draft')) {
            $availableData[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT] = $contentproxy->loadContent($id, $model, pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT);
        }

        return $availableData;
    }

    /**
     * @param int $id
     * @param boolean $hasPublishedVersion
     * @param boolean $hasDraftVersion
     * @return string
     */
    private function getEditStatus($id, $hasPublishedVersion, $hasDraftVersion)
    {
        if (!$id) pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED;

        return !$hasPublishedVersion && !$hasDraftVersion ?
                            pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED :
                            ($hasDraftVersion ? pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT : pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED);
    }

    /**
     * @param string $statusToEdit
     * @param boolean $hasPublishedVersion
     * @param boolean $hasDraftVersion
     * @return void
     */
    private function setButtonVisibility($statusToEdit, $canEdit, $canEditDraft)
    {
        $this->setComponentsVisibility(['savePublish'], $canEdit && $statusToEdit==pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT);
        $this->setComponentsVisibility(['save', 'saveClose'], $canEdit && $statusToEdit==pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED);
        $this->setComponentsVisibility(['saveDraft', 'saveDraftClose'], $canEditDraft);
    }


}
