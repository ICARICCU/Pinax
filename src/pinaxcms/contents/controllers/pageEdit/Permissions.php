<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_Permissions extends pinax_mvc_core_Command
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

        $menu = pinax_ObjectFactory::createModel('pinaxcms.contents.models.Menu');
        $menu->load($menuId);

        //inserire menu_extendsPermissions nella tabella menus_tbl
        $data = new StdClass;
        $data->extendsPermissions = $menu->menu_extendsPermissions;
        $tableName = $menu->getTableName();
        $aclBack = array();
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.contents.models.Role')
            ->load('getAclBack', array('menuId' => $menuId, 'tableName' => $tableName));

        foreach ($it as $ar) {
            $aclBack[] = array(
                'id' => $ar->role_id,
                'text' => $ar->role_name
            );
        }

        $aclFront = array();
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.contents.models.Role')
            ->load('getAclFront', array('menuId' => $menuId, 'tableName' => $tableName));

        foreach ($it as $ar) {
            $aclFront[] = array(
                'id' => $ar->role_id,
                'text' => $ar->role_name
            );
        }

        $data->aclBack = $aclBack;
        $data->aclFront = $aclFront;
        $this->view->setData($data);
    }
}
