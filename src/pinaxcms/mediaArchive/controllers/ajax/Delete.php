<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_ajax_Delete extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $ids = explode(',', __Request::get('ids'));
        if (!empty($ids)) {
            $mediaProxy = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.models.proxy.MediaProxy');
            foreach ($ids as $id) {
                $mediaProxy->deleteMedia($id);
            }
        }
    }
}
