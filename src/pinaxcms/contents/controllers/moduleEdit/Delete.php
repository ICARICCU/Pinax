<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_Delete extends pinax_mvc_core_Command
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($id, $model)
    {
        $this->checkPermissionForBackend();
        if (!$id || !$model) return false;

        $pageId = $this->application->getPageId();
        if (!$this->user->acl($pageId, 'visible', null, $id) || !$this->user->acl($pageId, 'delete', null, $id)) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $contentproxy->delete($id, $model);
        pinax_helpers_Navigation::goHere();
    }
}
