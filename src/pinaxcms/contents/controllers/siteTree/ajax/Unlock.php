<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_siteTree_ajax_Unlock extends pinax_mvc_core_CommandAjax
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($menuId) {
        $this->checkPermissionForBackend();
        if ($menuId) {
            $languageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
            $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
            $menuProxy->lockUnlock($menuId, false);
            return true;
        }

        return false;
    }
}
