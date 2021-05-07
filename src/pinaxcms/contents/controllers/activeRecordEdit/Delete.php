<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_activeRecordEdit_Delete extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($id, $model)
    {
        $this->checkPermissionForBackend();
        if ($id) {
            $proxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ActiveRecordProxy');
            $proxy->delete($id, $model);

            pinax_helpers_Navigation::goHere();
        }
    }
}
