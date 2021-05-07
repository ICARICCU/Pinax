<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_MediaManager extends PinaxObject
{
    /**
     * @param $id
     *
     * @return PinaxObject|null
     */
    public static function &getMediaById($id)
	{
		$ar = pinax_ObjectFactory::createModel('pinaxcms.models.Media');
		if ($ar->load($id))
		{
			$media = &pinaxcms_mediaArchive_MediaManager::getMediaByRecord($ar);
			return $media;
		}
		else
		{
			// TODO
			// ERRORE
		}

		return NULL;
	}


	/**
     * @param $ar
     *
     * @return PinaxObject
     */
    public static function &getMediaByRecord(&$ar)
	{
		$mediaType = $ar->media_type;
		$mediaTypeInfo = pinaxcms_mediaArchive_MediaManager::getMediaTypeInfo();
		$mediaClassName = 'pinaxcms.mediaArchive.media.'.$mediaTypeInfo[$mediaType]['class'];
		$media = &pinax_ObjectFactory::createObject($mediaClassName, $ar);
		return $media;
	}

    /**
     * @param $values
     *
     * @return PinaxObject
     */
	public static function &getMediaByValues( $values )
	{
		$mediaType = $values['media_type'];
		$mediaTypeInfo = pinaxcms_mediaArchive_MediaManager::getMediaTypeInfo();
		$mediaClassName = 'pinaxcms.mediaArchive.media.'.$mediaTypeInfo[$mediaType]['class'];
		$media = &pinax_ObjectFactory::createObject($mediaClassName, $values );
		return $media;
	}

	/**
     * @param $mediaType
     *
     * @return PinaxObject
     */
    public static function &getEmptyMediaByType($mediaType)
	{
		$mediaTypeInfo = pinaxcms_mediaArchive_MediaManager::getMediaTypeInfo();
		$mediaClassName = 'pinaxcms.mediaArchive.media.'.$mediaTypeInfo[$mediaType]['class'];
		$ar = null;
        /** @var pinaxcms_mediaArchive_media_Media $media */
		$media = &pinax_ObjectFactory::createObject($mediaClassName, null);
		$media->type = $mediaType;
		return $media;
	}

    /**
     * @return array
     */
	public static function getMediaTypeInfo()
	{
		$fileTypes = array(	'IMAGE' => 		array('extension' => array('jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff'), 'class' => 'Image'),
							'OFFICE' => 	array('extension' => array('doc', 'xls', 'mdb', 'ppt', 'pps', 'html', 'htm', 'odb', 'odc', 'odf', 'odg', 'odi', 'odm', 'odp', 'ods', 'odt', 'otc', 'otf', 'otg', 'oth', 'oti', 'otp', 'ots', 'ott', 'docx', 'dotx', 'xlsx', 'xltx', 'pptx', 'potx'), 'class' => 'Office'),
							'ARCHIVE' => 	array('extension' => array('zip', 'rar', '7z', 'tar', 'gz', 'tgz'), 'class' => 'Archive'),
							'AUDIO' => 		array('extension' => array('wav', 'mp3', 'aif'), 'class' => 'Audio'),
							'PDF' => 		array('extension' => array('pdf'), 'class' => 'Pdf'),
							'VIDEO' => 		array('extension' => array('avi', 'mov', 'flv', 'wmv', 'mp4', 'm4v', 'mpg'), 'class' => 'Video'),
							'FLASH' => 		array('extension' => array('swf'), 'class' => 'Flash'),
							'OTHER' => 		array('extension' => array(), 'class' => 'Other'),
						);

		$customType = pinaxcms_mediaArchive_MediaManager::getCustomMediaType();
		if (count($customType))
		{
			$fileTypes = array_merge($fileTypes, $customType);
		}

		return $fileTypes;
	}

    /**
     * @param $ext
     *
     * @return int|string
     */
	public static function getMediaTypeFromExtension($ext)
	{
		$ext = strtolower($ext);
		$fileTypes = pinaxcms_mediaArchive_MediaManager::getMediaTypeInfo();

		$fileType = 'OTHER';
		foreach($fileTypes as $k=>$v)
		{
			if (in_array($ext, $v['extension']))
			{
				$fileType = $k;
				break;
			}
		}
		return $fileType;
	}

    /**
     * @param $type
     * @param $values
     */
	public static function addCustomMediaType($type, $values)
	{
		$customType = &pinax_ObjectValues::get('pinax.MediaManager', 'customType', array());
		$customType[$type] = $values;
	}

    /**
     * @return string
     */
	public static function getCustomMediaType()
	{
		$customType = &pinax_ObjectValues::get('pinax.MediaManager', 'customType', array());
		return $customType;
	}
}
