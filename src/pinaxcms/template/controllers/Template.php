<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_controllers_Template extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
        if (!$menuId) {
            throw pinaxcms_core_application_ApplicationException::noMenuId();
        }
    }
}
