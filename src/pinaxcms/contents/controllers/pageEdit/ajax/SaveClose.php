<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_SaveClose extends pinaxcms_contents_controllers_pageEdit_ajax_Save
{
   use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $r = parent::execute($data);

        if ($r===true) {
            $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
            $arMenu = $menuProxy->getMenuFromId($this->menuId, pinax_ObjectValues::get('org.pinax', 'editingLanguageId'));
            if ($arMenu->menu_type == pinaxcms_core_models_enum_MenuEnum::BLOCK) {
                return array('evt' => 'pinaxcms.pageEdit', 'message' => array('menuId' => $arMenu->menu_parentId));
            }
        }

        return $r;
    }
}
