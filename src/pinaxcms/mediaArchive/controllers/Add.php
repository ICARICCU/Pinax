<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_Add extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute()
    {
        $this->checkPermissionForBackend();

        $c = $this->view->getComponentById('fileuploader');
        if ($c) {
            $c->setAttribute('data', ';maxlabel='.ini_get('upload_max_filesize').'B', true);
        }

        if (!__Config::get('pinaxcms.mediaArchive.mediaMappingEnabled')) {
            $c = $this->view->getComponentById('addFromServer');
            if ($c) {
                $c->setAttribute('label', null);
                $c->setAttribute('visible', false);
            }
        }

        if (!__Config::get('pinaxcms.mediaArchive.addFromZipEnabled')) {
            $c = $this->view->getComponentById('addFromZip');
            if ($c) {
                $c->setAttribute('label', null);
                $c->setAttribute('visible', false);
            }
        }

    }
}


