<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_roleManager_services_RoleService extends PinaxObject
{
	function addModule($moduleId, $permission = array('visible' => 'true'))
	{
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.roleManager.models.Role');

        foreach ($it as $ar) {
            $permissions = unserialize($ar->role_permissions);
            $permissions[$moduleId] = $permission;
            $ar->role_permissions = serialize($permissions);
            $ar->save();
        }
	}
}
