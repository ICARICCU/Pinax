<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_roleManager_controllers_StateController extends pinax_components_StateSwitchClass
{
    function execute_edit($oldState)
    {
        $id = __Request::get('dataGridEdit_recordId');
        if ($id && strtolower( __Request::get( 'action', '' ) ) != 'next') {
            $ar = pinax_ObjectFactory::createModel('pinaxcms.roleManager.models.Role');
            $ar->load($id);
            __Request::set('roleId', $ar->role_id);
            __Request::set('roleName', $ar->role_name);
            __Request::set('roleActive', $ar->role_active);
            __Request::set('permissions', $ar->role_permissions);

            $groups = array();

            $it = pinax_ObjectFactory::createModelIterator('pinaxcms.roleManager.models.Group', 'getGroups', array('params' => array('roleId' => $id)));

            foreach ($it as $ar) {
                $groups[] = array(
                    'id' => $ar->join_FK_dest_id,
                    'text' => $ar->usergroup_name
                );
            }

            $users = array();

            $it = pinax_ObjectFactory::createModelIterator('pinaxcms.roleManager.models.User', 'getUsers', array('params' => array('roleId' => $id)));

            foreach ($it as $ar) {
                $users[] = array(
                    'id' => $ar->join_FK_dest_id,
                    'text' => $ar->user_loginId
                );
            }

            __Request::set('groups', $groups);
            __Request::set('users', $users);
        }
    }

    function executeLater_edit($oldState)
    {
        $this->executeLater_new($oldState);
    }

    function executeLater_new( $oldState )
    {
        // controlla se � stato ftto submit
        if ( strtolower( __Request::get( 'action', '' ) ) == 'next' )
        {
            if ($this->_parent->validate()) {
                $roleId = __Request::get('roleId');
                $roleName = __Request::get('roleName');
                $groups = __Request::get('groups');
                $users = __Request::get('users');
                $roleActive = __Request::get('roleActive');
                $permissions = __Request::get('permissions');
                $aclPageTypes = __Request::get('aclPageTypes');

                foreach ((array)$aclPageTypes as $masterPage => $pages) {
                    $pages = explode(',', $pages);

                    foreach ($pages as $page) {
                        $page = strtolower($page);

                        // se già sono settati permessi specifici non vengon copiati dal pagetype master
                        if ($permissions[$page]) continue;

                        // copia i permessi del pagetype master
                        $permissions[$page] = $permissions[$masterPage];
                    }
                }

                $ar = pinax_ObjectFactory::createModel('pinaxcms.roleManager.models.Role');
                if ($roleId) $ar->load($roleId);
                $ar->role_name = $roleName;
                $ar->role_active = $roleActive;
                $ar->role_permissions = serialize($permissions);

                if ($roleId) {
                    $ar->save();
                }
                else {
                    $roleId = $ar->save();
                }

                $ar = pinax_ObjectFactory::createModel('pinax.models.Join');
                $ar->delete(array('join_FK_source_id' => $roleId, 'join_objectName' => 'roles2usergroups'));
                $ar->delete(array('join_FK_source_id' => $roleId, 'join_objectName' => 'roles2users'));

                if ($groups != '') {
                    $groups = explode(',', $groups);

                    foreach ($groups as $groupId) {
                        $ar->emptyRecord();
                        $ar->join_FK_source_id = $roleId;
                        $ar->join_FK_dest_id = $groupId;
                        $ar->join_objectName = 'roles2usergroups';
                        $ar->save(null, true);
                    }
                }

                if ($users != '') {
                    $users = explode(',', $users);

                    foreach ($users as $userId) {
                        $ar->emptyRecord();
                        $ar->join_FK_source_id = $roleId;
                        $ar->join_FK_dest_id = $userId;
                        $ar->join_objectName = 'roles2users';
                        $ar->save(null, true);
                    }
                }

                __Session::remove('pinax.roles');

                $this->_parent->refreshToState( 'reset' );
            }
        }
    }

    function execute_delete( $oldState )
    {
        $id = __Request::get('dataGridEdit_recordId');
        if ($id) {
            $ar = pinax_ObjectFactory::createModel('pinaxcms.roleManager.models.Role');
            $ar->delete($id);
            $this->_parent->refreshToState( 'reset' );
        }
    }
}
