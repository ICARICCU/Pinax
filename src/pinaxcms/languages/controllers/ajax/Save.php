<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_languages_controllers_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;
        $data = json_decode($data);

        $proxy = pinax_ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
        if ($proxy->findLanguageByCountry($data->language_FK_country_id, @$data->__id)) {
            return array('errors' => array(__T('LANGUAGE_ALREADY_PRESENT')));
        }

        $result = $proxy->save($data);

        if ($result['__id']) {
            return array('set' => $result);
        } else {
            return array('errors' => $result);
        }
    }
}
