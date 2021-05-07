<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_controllers_CheckTemplateTabDraw extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
        $templateEnabled = __Config::get('pinaxcms.contents.templateEnabled');
        if ($templateEnabled) {
            $templateProxy = pinax_ObjectFactory::createObject('pinaxcms.template.models.proxy.TemplateProxy');
            $templateEnabled = $templateProxy->getTemplateAdmin()!==false && $this->user->acl('templateselect', 'all');
        }
        $this->view->setAttribute('draw', $templateEnabled);
    }
}
