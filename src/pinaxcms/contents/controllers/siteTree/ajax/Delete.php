<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_siteTree_ajax_Delete extends pinax_mvc_core_CommandAjax
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $menuProxy->deleteMenu($menuId);

        return true;
    }
}
