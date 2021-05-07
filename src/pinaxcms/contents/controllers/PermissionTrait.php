<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait pinaxcms_contents_controllers_PermissionTrait
{
    private $aclEnabled;

    /**
     * @param  int $menuId
     * @param  array $userRolesId
     * @param  boolean $editAllRole
     * @param  boolean $editRole
     * @return boolean
     */
    private function canEdit($menuId, $userRolesId, $editAllRole, $editRole) {
        if (!$this->aclEnabled) return true;

        $it = pinax_ObjectFactory::createModelIterator( 'pinax.models.Join')
                ->load('loadRelations', [
                                            'name' => __Config::get('DB_PREFIX') . 'menus_tbl#rel_aclBack',
                                            'source' => $menuId,
                                            'dest' => '']);


        if (!$it->count()) {
            return $editAllRole || ($editRole && __Config::get('pinaxcms.contentsedit.default.acl'));
        } else if ($it->count() && !count($userRolesId)) {
            return false;
        }

        $pageRolesId = array();
        foreach ($it as $ar) {
            $pageRolesId[] = $ar->join_FK_dest_id;
        }

        return $editAllRole || count(array_intersect($pageRolesId, $userRolesId)) > 0 && $editRole;
    }


    private function setAclFlag() {
        $this->aclEnabled = __Config::get( 'ACL_ENABLED' );
    }

    /**
     * @param  int $menuId
     */
    private function checkPageEditAndShowError($menuId) {
        $canEdit = $this->canEdit($menuId, $this->user->getRoles(), $this->user->acl('pinaxcms_contentsedit', 'all'), $this->user->acl('pinaxcms_contentsedit', 'edit') || $this->user->acl('pinaxcms_contentsedit', 'editDraft'));
        if (!$canEdit) {
           pinax_helpers_Navigation::accessDenied(true);
        }
    }
}
