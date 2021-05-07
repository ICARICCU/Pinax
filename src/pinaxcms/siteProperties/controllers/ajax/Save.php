<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_siteProperties_controllers_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    // TODO creare un proxy per gestire le proprietà del sito
    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $data = json_decode($data);
        $newData = array();
        $newData['title'] = $data->title;
        $newData['address'] = $data->address;
        $newData['copyright'] = $data->copyright;
        $newData['slideShow'] = $data->slideShow;
        $newData['analytics'] = $data->analytics;

        pinax_Registry::set(__Config::get('REGISTRY_SITE_PROP').$this->application->getEditingLanguage(), serialize($newData));
        return true;
    }
}
