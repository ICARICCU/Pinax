<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_Addblock extends pinaxcms_contents_controllers_pageEdit_Add
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
        parent::execute($menuId);

        // serve per impostare i filtri sui pagetype
        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $arMenu = $menuProxy->getMenuFromId($menuId, pinax_ObjectValues::get('org.pinax', 'editingLanguageId'));
        $this->setComponentsAttribute('pageParent', 'data', 'options='.$arMenu->menu_pageType);
    }
}
