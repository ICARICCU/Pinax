<?php

use Hashids\Hashids;

/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_BridgeNew extends pinaxcms_mediaArchive_Bridge
{
    public function mediaByIdUrl($id)
    {
        $media = pinaxcms_mediaArchive_MediaManager::getMediaById($id);
        if ($media) {
            $hashGenerator = __ObjectFactory::createObject('pinax.helpers.HashGenerator');
            $hash = $hashGenerator->encode($id);
            return __Link::makeURL('bridge-download-media', array('hash' => $hash, 'filename' => $media->originalFileName));
        }
    }
}
