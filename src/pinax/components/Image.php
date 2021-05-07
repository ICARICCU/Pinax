<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Image extends pinax_components_Component
{
	var $imageInfo;
	var $media;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('crop',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cropOffset',	false, 	0,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('height',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('width',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('zoom',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('superZoom',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('superZoomLabel',false, 	'{i18n:zoom}', COMPONENT_TYPE_STRING);
		$this->defineAttribute('imageInfo',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('processThumbnail',	false, 'true',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('group',			false, '',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('checkIfExists',			false, true,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:mediaType',        false,     'IMAGE',    COMPONENT_TYPE_STRING);
		$this->defineAttribute('title', 		false,  NULL,   COMPONENT_TYPE_STRING);
		$this->defineAttribute('role',			false,	NULL, COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->resetContent();
        $mediaId = $this->_parent->loadContent($this->getId());

        if (is_string($mediaId)) {
            $mediaId = json_decode($mediaId);
        }

        if (is_object($mediaId)) {
        	$mediaId = pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaIdFromJson($mediaId);
        }

        if (is_numeric($mediaId) && $mediaId > 0) {
        	$this->attachMedia($mediaId);
        }
	}

	function getContent($parent=NULL)
	{
		if (!is_null($parent) && $parent=='pnx:RecordSetList')
		{
			// elimina alcuni attributi
			// perch� l'immagine � visualizzata all'interno di un recordsetlist
			unset($this->_content['zoom']);
			unset($this->_content['onclick']);
			$this->setAttribute( 'zoom', false );
		}

		$this->_render_html();
		$this->_content['__media__'] = $this->media;
		return $this->_content;
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		$this->_render_html();
		$this->addOutputCode($this->_content['__html__']);
	}

	function _render_html()
	{
		if ($this->_content['mediaId']>0)
		{
			$attributes = $this->_content;

			if (!is_null($this->getAttribute('width')) && !is_null($this->getAttribute('height')))
			{
				if ($this->getAttribute('processThumbnail') != 'false' )
				{
					if ( $this->getAttribute('processThumbnail') == 'full' )
					{
						$thumbnail = $this->media->getResizeImage($this->getAttribute('width'), $this->getAttribute('height'), $this->getAttribute('crop'), $this->getAttribute('cropOffset'));
					}
					else
					{
						$thumbnail = $this->media->getThumbnail($this->getAttribute('width'), $this->getAttribute('height'), $this->getAttribute('crop'), $this->getAttribute('cropOffset'));
					}
					$attributes['src'] 				= $thumbnail['fileName'];
					$attributes['width'] 			= $thumbnail['width'];
					$attributes['height'] 			= $thumbnail['height'];
					$this->_content['src'] 			= $thumbnail['fileName'];
					$this->_content['width'] 		= $thumbnail['width'];
					$this->_content['height'] 		= $thumbnail['height'];
				}
				else
				{
					$attributes['src'] = pinax_helpers_Media::getImageUrlById($this->_content['mediaId'], $this->getAttribute('width'), $this->getAttribute('height'), $this->getAttribute('crop'));
					$this->_content['src'] = $attributes['src'];

				}
			}

			$attributes['src'] = str_replace(' ', '%20', $attributes['src']);
			$this->_content['src'] = str_replace(' ', '%20', $this->_content['src']);

			$useZoom = $attributes['zoom'];
			unset($attributes['description']);
			unset($attributes['mediaId']);
			unset($attributes['zoom']);
			unset($attributes['size']);
			unset($attributes['mediaType']);
			unset($attributes['originalSrc']);
			unset($attributes['__media__']);
			$this->_content['__html__'] = '<img '.$this->_renderAttributes($attributes, null, ['alt']).'/>';

			if ( $this->getAttribute( 'zoom' ))
			{
				if ($useZoom && $this->getAttribute('superZoom')) {
					$this->_application->addZoomJsCode();
					$this->_content['__html__'] .= '<span class="superzoom js-pinaxSuperZoom" data-mediaid="'.$this->media->id.'" data-mediawatermark="'.($this->media->watermark ? '1' : '0').'">'.$this->getAttribute('superZoomLabel').'</span>';
				}

				$this->_application->addLightboxJsCode();
				$attributes = array();
				$attributes['title'] = $this->_content['title'];
				if ($this->media->type=='IMAGE') {
					$attributes['class'] = 'js-lightbox-image';
					$attributes['href'] = pinaxcms_helpers_Media::getImageUrlById($this->media->id, __Config::get( 'IMG_WIDTH_ZOOM' ), __Config::get( 'IMG_HEIGHT_ZOOM' ));
				} else {
					$attributes['class'] = 'js-lightbox-inline';
					$attributes['href'] = pinaxcms_helpers_Media::getFileUrlById($this->media->id);
				}
				$attributes['data-type'] = strtolower($this->media->type);
				$attributes['rel'] = $this->getAttribute( 'group' );

				$this->_content['__html__'] = pinax_helpers_Html::renderTag( 'a', $attributes, true, $this->_content['__html__'] );
			}
		}
		else
		{
			$this->_content['__html__'] = '';
		}
	}


	function resetContent($childrensReset=false)
	{
		$this->_content = array();
		$this->_content['mediaId']			= 0;
		$this->_content['src'] 				= '';
		$this->_content['alt'] 				= '';
		$this->_content['title'] 			= '';
		$this->_content['description'] 			= '';
		$this->_content['class'] 			= '';
		$this->_content['style'] 			= '';
		$this->_content['onclick'] 			= '';
		$this->_content['size'] 			= '';
		$this->_content['mediaType'] 			= '';
		$this->_content['zoom']	= '';
		$this->_content['role'] = '';
	}

	function attachMedia($mediaId)
	{
		$this->media = &pinaxcms_mediaArchive_MediaManager::getMediaById($mediaId);
		if (is_object($this->media))
		{
			if ( $this->getAttribute( 'imageInfo' ) && !$this->_application->isAdmin() )
			{
				$this->imageInfo = $this->media->getImageInfo();
				$width = $this->imageInfo['width'];
				$height = $this->imageInfo['height'];
			}
			else
			{
				$width = '';
				$height = '';
			}

			$title = $this->getAttribute('title');
			$role = $this->getAttribute('role');
			$this->_content = array();
			$this->_content['mediaId']		= $this->media->id;
			$this->_content['src'] 			= $this->media->getFileName( $this->getAttribute( 'checkIfExists' ) );
			$this->_content['originalSrc'] 	= $this->_content['src'];
			$this->_content['alt']          = is_null($title) ? $this->media->title : $title;
			$this->_content['title']        = is_null($title) ? $this->media->title : $title;
			$this->_content['description'] 	= $this->media->description;
			$this->_content['zoom'] 		= $this->media->zoom;
			$this->_content['class'] 		= $this->getAttribute('cssClass');
			$this->_content['style'] 		= "";
			$this->_content['onclick'] 		= "";
			$this->_content['width'] 		= $width;
			$this->_content['height'] 		= $height;
			$this->_content['size'] 		= $this->media->size;
			$this->_content['mediaType'] 	= $this->media->type;
			if (!is_null($role)) {
				$this->_content['role'] = $role;
			}

			/*
			if ($this->getAttribute('zoom')==true)
			{
				$this->_content['onclick'] 	= 'Pinax.previewImage(\''.$this->media->id.'\');';
				$this->_content['style'] 	= 'cursor: pointer';
			}
			*/

		}
	}

	function getOGvalue() {
		return PNX_HOST . '/' . pinaxcms_helpers_Media::getImageUrlById($this->_content['mediaId'], $this->getAttribute('width'), $this->getAttribute('height'), $this->getAttribute('crop'), $this->getAttribute('cropOffset'));
	}

	public static function translateForMode_edit($node) {
		$mediaType = $node->hasAttribute('adm:mediaType') ? $node->getAttribute('adm:mediaType') : 'IMAGE';
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['data'] = $node->getAttribute('data').';type=mediapicker;mediatype='.$mediaType.';preview=true';
		$attributes['xmlns:pnx'] = "pinax.components.*";

		if (count($node->attributes))
		{
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->prefix=="adm")
				{
					$attributes[$attr->name] = $attr->value;
				}
			}
		}

		return pinax_helpers_Html::renderTag('pnx:Input', $attributes);
	}
}
