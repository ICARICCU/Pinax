<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_Properties extends pinax_mvc_core_Command
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

        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        $menu->load($menuId);
        $data = $menu->getValuesAsArray();
        // TODO controllase se il componente deve essere nascosto
        // quando ci sono pagine che devono essere usate una sola volta
        $this->setComponentsAttribute('menu_pageType', 'hide', $menu->menu_type == 'SYSTEM');

        $menuDetail = pinax_ObjectFactory::createModel('pinaxcms.core.models.MenuDetail');
        $menuDetail->find(array('menudetail_FK_menu_id' => $menuId, 'menudetail_FK_language_id' => pinax_ObjectValues::get('org.pinax', 'editingLanguageId')));
        $data = array_merge($data, $menuDetail->getValuesAsArray());
        $data['menu_creationDate'] = preg_replace('/ \d{2}:\d{2}:\d{2}/', '', $data['menu_creationDate']);

        $this->setComponentsVisibility('menudetail_hideInNavigation', $menuDetail->fieldExists('menudetail_hideInNavigation'));

        if ($menu->menu_parentId) {
            $menuParent = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
            $menuParent->load($menu->menu_parentId);
            $data['menu_parentPageType'] = $menuParent->menu_pageType;
        }

        if ($this->user->acl('pinaxcms', 'page.properties.modifyPageTypeFree')) {
            $this->setComponentsAttribute('menu_pageType', 'linked', '');
        }

        $this->view->setData($data);

    }
}
