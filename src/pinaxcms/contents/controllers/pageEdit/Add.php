<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_Add extends pinax_mvc_core_Command
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($menuId)
    {
        $this->checkPermissionForBackend($this->application->getPageId(), 'new');
        if (!$menuId) {
            $this->changeAction('index');
        }

        $this->setComponentsAttribute('pageParent', 'value', $menuId);
    }
}
