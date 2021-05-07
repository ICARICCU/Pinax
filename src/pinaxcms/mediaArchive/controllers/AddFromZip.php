<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_AddFromZip extends pinaxcms_mediaArchive_controllers_Add
{
    public function execute()
    {
        parent::execute();
        $this->setComponentsVisibility('media_title', false);
    }
}


