<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_Delete extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($id)
    {
        $this->checkPermissionForBackend();
        $mediaProxy = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.models.proxy.MediaProxy');
        $mediaProxy->deleteMedia($id);

        pinax_helpers_Navigation::goHere();
    }
}
