<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_activeRecordEdit_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $proxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ActiveRecordProxy');
        $result = $proxy->save(pinax_maybeJsonDecode($data, false));

        $this->directOutput = true;

        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errorFields' => $result);
        }
    }
}
