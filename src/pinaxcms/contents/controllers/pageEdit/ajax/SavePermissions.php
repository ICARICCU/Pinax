<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_SavePermissions extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    protected $tableName;
    protected $menuProxy;
    protected $languageId;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->languageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
        $this->menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');


        $data = json_decode($data);
        $aclBack = implode(',', $data->aclBack);
        $aclFront = implode(',', $data->aclFront);

        $ar = pinax_ObjectFactory::createModel('pinaxcms.contents.models.Menu');
        $ar->load($data->menuId);
        $removeExtendsPermissions = $ar->menu_extendsPermissions && !$data->extendsPermissions;
        $ar->menu_extendsPermissions = $data->extendsPermissions;
        $ar->menu_isLocked = $aclFront ? 1 : 0;
        $ar->save();

        $this->tableName = $ar->getTableName();
        $this->setACL($data->menuId, $aclBack, $aclFront);

        // se extendsPermissions è true bisogna salvare i permessi in JoinDoctrine,
        // ricorsivamente anche alle pagine figlie
        if ($data->extendsPermissions) {
            $this->extendPermissions($data->menuId, $aclBack, $aclFront);
        }

        if ($removeExtendsPermissions) {
            $this->removeExtendPermissions($data->menuId);
        }

        $this->menuProxy->invalidateSitemapCache();
        $this->directOutput = true;
        return array('evt' => 'pinaxcms.refreshTree', 'message' => '');
    }

    /**
     * @param int $menuId
     * @param string $aclBack
     * @param string $aclFront
     * @return void
     */
    protected function extendPermissions($menuId, $aclBack, $aclFront)
    {
        $itMenus = $this->menuProxy->getChildMenusFromId($menuId, $this->languageId);
        foreach($itMenus as $subMenu) {
            $subMenu->menu_extendsPermissions = 1;
            $subMenu->menu_isLocked = $aclFront ? 1 : 0;
            $subMenu->save();

            $this->setACL($subMenu->menu_id, $aclBack, $aclFront);
            $this->extendPermissions($subMenu->menu_id, $aclBack, $aclFront);
        }
    }

    /**
     * @param int $menuId
     * @param string $aclBack
     * @param string $aclFront
     * @return void
     */
    protected function setACL($menuId, $aclBack, $aclFront)
    {
        $ar = pinax_ObjectFactory::createModel('pinax.models.JoinDoctrine');
        $ar->delete(array('join_objectName' => $this->tableName.'#rel_aclBack', 'join_FK_source_id' => $menuId));
        $ar->delete(array('join_objectName' => $this->tableName.'#rel_aclFront', 'join_FK_source_id' => $menuId));

        if ($aclBack != '') {
            $aclBack = explode(',', $aclBack);
            foreach ($aclBack as $role) {
                $ar->emptyRecord();
                $ar->join_FK_source_id = $menuId;
                $ar->join_FK_dest_id = $role;
                $ar->join_objectName = $this->tableName.'#rel_aclBack';
                $ar->save(null, true);
            }
        }

        if ($aclFront != '') {
            $aclFront = explode(',', $aclFront);

            foreach ($aclFront as $role) {
                $ar->emptyRecord();
                $ar->join_FK_source_id = $menuId;
                $ar->join_FK_dest_id = $role;
                $ar->join_objectName = $this->tableName.'#rel_aclFront';
                $ar->save(null, true);
            }
        }
    }

    /**
     * @param int $menuId
     * @return void
     */
    protected function removeExtendPermissions($menuId)
    {
        $itMenus = $this->menuProxy->getChildMenusFromId($menuId, $this->languageId);
        foreach($itMenus as $subMenu) {
            $subMenu->menu_extendsPermissions = 0;
            $subMenu->save();

            $this->removeExtendPermissions($subMenu->menu_id);
        }
    }
}
