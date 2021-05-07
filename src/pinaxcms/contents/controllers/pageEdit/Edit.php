<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_Edit extends pinax_mvc_core_Command
{
	use pinax_mvc_core_AuthenticatedCommandTrait;
    use pinaxcms_contents_controllers_PermissionTrait;

    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
        if (!$menuId) {
            $this->changeAction('index');
        }

        $this->setAclFlag();
        $this->checkPageEditAndShowError($menuId);

        $editingLanguageId = $this->application->getEditingLanguageId();
        $editingLanguage = $this->application->getEditingLanguage();

        // controlla se il menù è di tipo Block
        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $arMenu = $menuProxy->getMenuFromId($menuId, $editingLanguageId);

        $previewUrl = PNX_HOST.'/../?pageId='.($arMenu->menu_type != pinaxcms_core_models_enum_MenuEnum::BLOCK ? $menuId : $arMenu->menu_parentId).
        '&language='.$editingLanguage;

        $this->setComponentsAttribute('preview', 'visible', true);
        $this->setComponentsAttribute('preview', 'url', $previewUrl);
        $this->setComponentsVisibility('saveAndClose', $arMenu->menu_type == pinaxcms_core_models_enum_MenuEnum::BLOCK);

        if ($arMenu->menu_type == pinaxcms_core_models_enum_MenuEnum::BLOCK) {
            $this->view->setAttribute('editUrl', false);
            $this->setComponentsAttribute('propertiesState', 'draw', false);
            $this->setComponentsAttribute('templateState', 'draw', false);

            $breadCrumbs = array($arMenu->menudetail_title);
            while ($arMenu->menu_type == pinaxcms_core_models_enum_MenuEnum::BLOCK) {
                $arMenu = $menuProxy->getMenuFromId($arMenu->menu_parentId, $editingLanguageId);
                $breadCrumbs[] = '<a href="#" data-id="'.$arMenu->menu_id.'" class="js-pinaxcms-menu-edit">'.$arMenu->menudetail_title.'</a>';
            }

            $this->view->resetPageTitleModifier();
            $this->view->addPageTitleModifier(new pinaxcms_views_components_FormEditPageTitleModifierVO(
                            'edit',
                            __T('Edit page', implode(' > ', array_reverse($breadCrumbs))),
                            false,
                            '__id',
                            ''));
        }

    }

    public function executeLater($menuId)
    {
        $statusToEdit = $this->view->statusToEdit();
        $availableStatus = $this->view->availableStatus();

        if (!$availableStatus) {
            $this->setComponentsVisibility(array('saveDraft', 'savePublish'), false);
        } else {
            $this->setComponentsVisibility(array('savePublish'), $statusToEdit==pinaxcms_contents_views_components_PageEdit::STATUS_DRAFT);
            $this->setComponentsVisibility(array('save'), $statusToEdit==pinaxcms_contents_views_components_PageEdit::STATUS_PUBLISHED);
        }
    }

}
