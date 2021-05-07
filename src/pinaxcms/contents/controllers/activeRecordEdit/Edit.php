<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_activeRecordEdit_Edit extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($id)
    {
        $this->checkPermissionForBackend();

        if ($id) {
            $c = $this->view->getComponentById('__model');
            $model = $c->getAttribute('value');
            $proxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ActiveRecordProxy');
            $data = $proxy->load($id, $model);

            $data['__id'] = $id;
            $this->view->setData($data);
        }
    }
}
