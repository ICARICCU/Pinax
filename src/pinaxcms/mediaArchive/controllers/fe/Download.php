<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hashids\Hashids;

class pinaxcms_mediaArchive_controllers_fe_Download extends pinax_mvc_core_Command
{
    public function execute($hash, $filename)
    {
        $hashGenerator = __ObjectFactory::createObject('pinax.helpers.HashGenerator');
        $id = $hashGenerator->decode($hash);
        pinaxcms_Pinaxcms::getMediaArchiveBridge()->serveMedia($id);
    }
}
