<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_controllers_ajax_SaveTemplate extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $data = json_decode($data);
        if ($data && property_exists($data, 'template')) {
            $templateProxy = pinax_ObjectFactory::createObject('pinaxcms.template.models.proxy.TemplateProxy');
            $templateProxy->setSelectedTemplate($data->template);
            pinax_cache_CacheFile::cleanPHP(__Paths::get('APPLICATION_TO_ADMIN_CACHE'));
            return true;
        }
        return false;
    }
}
