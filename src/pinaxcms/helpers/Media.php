<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_helpers_Media extends PinaxObject
{

	public static function getImageById($id, $direct=false, $cssClass='', $style='', $onclick='')
	{
		$media = &pinaxcms_mediaArchive_MediaManager::getMediaById($id);
		if (is_null($media)) {
			return '';
		}
		$attributes = array();
		$attributes['alt'] = $media->title;
		$attributes['title'] = $media->title;
		$attributes['class'] = $cssClass;
		$attributes['style'] = $style;
		$attributes['onclick'] = $onclick;
		$attributes['src'] = $direct ? $media->getFileName() : pinaxcms_Pinaxcms::getMediaArchiveBridge()->imageByIdUrl($id);
		;
		return pinax_helpers_Html::renderTag('img', $attributes);
	}


	public static function getResizedImageById($id, $direct=false, $width, $height, $crop=false, $cssClass='', $style='', $onclick='')
	{
		$media = &pinaxcms_mediaArchive_MediaManager::getMediaById($id);
		if (is_null($media)) {
			return '';
		}
		if ($direct) {
			$thumb = $media->getThumbnail($width, $height, $crop);
		}
		$attributes = array();
		$attributes['alt'] = $media->title;
		$attributes['title'] = $media->title;
		$attributes['class'] = $cssClass;
		$attributes['style'] = $style;
		$attributes['onclick'] = $onclick;
		$attributes['src'] = $direct ? $thumb['fileName'] : pinaxcms_Pinaxcms::getMediaArchiveBridge()->imageByIdAndResizedUrl($id, $width, $height, $crop);
		;
		return pinax_helpers_Html::renderTag('img', $attributes);
	}

	public static function getImageUrlById($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false )
	{
		return pinaxcms_Pinaxcms::getMediaArchiveBridge()->imageByIdAndResizedUrl($id, $width, $height, $crop, $cropOffset, $forceSize, $useThumbnail);
	}

	public static function getResizedImageUrlById($id, $direct=false, $width, $height, $crop=false, $cropOffset=1, $forceSize=false )
	{
		if ($direct) {
			$media = &pinaxcms_mediaArchive_MediaManager::getMediaById($id);
			if (is_null($media)) {
				return '';
			}
			$thumb = $media->getThumbnail($width, $height, $crop, $cropOffset, $forceSize );
			return $thumb['fileName'];
		}
		return self::getImageUrlById( $id, $width, $height, $crop, $cropOffset, $forceSize );
	}

	public static function getUrlById($id, $direct=false)
	{
		if ($direct) {
			$media = &pinaxcms_mediaArchive_MediaManager::getMediaById($id);
			return is_null($media) ? '' : $media->getFileName();
		} else {
			return pinaxcms_Pinaxcms::getMediaArchiveBridge()->imageByIdUrl($id);
		}
	}

	public static function getFileUrlById($id, $direct=false)
	{
		if ($direct) {
			$media = &pinaxcms_mediaArchive_MediaManager::getMediaById($id);
			return is_null($media) ? '' : $media->getFileName(false);
		} else {
			return pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaByIdUrl($id);
		}
	}

	/* deprecated */
	public static function getThumbnailById($id, $width, $height, $crop=false, $class='', $onclick='')
	{
		return self::getResizedImageById($id, false, $width, $height, $crop, $class, '', '');
	}
}

class __Media extends pinaxcms_helpers_Media
{
}
