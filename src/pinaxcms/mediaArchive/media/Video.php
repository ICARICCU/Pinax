<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_media_Video extends pinaxcms_mediaArchive_media_Media
{
	function getIconFileName()
	{
		return pinax_Assets::get('ICON_MEDIA_VIDEO');
	}
}
