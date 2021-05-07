<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_fe_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'pinaxcms.userManager.fe';
        $moduleVO->name = __T('User Manager');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'org.pinaxcms.userManager.fe';
        $moduleVO->author = 'PINAX';
        $moduleVO->authorUrl = '';

        pinax_Modules::addModule( $moduleVO );
    }
}
