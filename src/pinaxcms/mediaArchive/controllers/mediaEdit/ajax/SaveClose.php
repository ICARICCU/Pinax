<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_mediaEdit_ajax_SaveClose extends pinaxcms_mediaArchive_controllers_mediaEdit_ajax_Save
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data, $mediaType='')
    {
        $this->checkPermissionForBackend();

        $result = parent::execute($data);

        if ($result['errors']) {
            return $result;
        }


        $this->directOutput = true;
        return array('url' => $this->changeAction('').($mediaType ? '?mediaType='.$mediaType : ''));
    }
}
