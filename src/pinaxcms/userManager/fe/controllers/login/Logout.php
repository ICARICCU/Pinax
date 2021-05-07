<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_fe_controllers_login_Logout extends pinax_mvc_core_Command
{
    public function execute()
    {
        $authClass = pinax_ObjectFactory::createObject(__Config::get('pinax.authentication'));
        $authClass->logout();

        pinax_helpers_Navigation::gotoUrl( PNX_HOST );
        exit();
    }
}
