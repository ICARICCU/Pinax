<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_controllers_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $templateProxy = pinax_ObjectFactory::createObject('pinaxcms.template.models.proxy.TemplateProxy');
        $templateProxy->saveEditData($data);

        return true;
    }
}
