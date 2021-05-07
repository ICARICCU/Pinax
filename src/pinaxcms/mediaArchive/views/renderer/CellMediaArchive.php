<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_views_renderer_CellMediaArchive extends pinax_components_render_RenderCellRecordSetList
{
	public function renderCell( &$ar, $params )
	{
		$mediaBridge = pinaxcms_Pinaxcms::getMediaArchiveBridge();
		$media = pinaxcms_mediaArchive_MediaManager::getMediaByRecord( $ar );
		$ar->thumb_filename = $mediaBridge->imageByIdAndResizedUrl(
											$ar->media_id,
											__Config::get('THUMB_WIDTH'),
											__Config::get('THUMB_HEIGHT'),
											__Config::get('ADM_THUMBNAIL_CROP'),
											__Config::get('ADM_THUMBNAIL_CROPPOS'));

		$sizes = method_exists( $media, 'getOriginalSizes') ? $media->getOriginalSizes() : array( 'width' => 0, 'height' => 0 );
		$ar->media_w = $sizes['width'];
		$ar->media_h = $sizes['height'];
		if ($ar->media_title==='') {
			$ar->media_title = $ar->media_originalFileName;
		}
		if (!$ar->media_date) $ar->media_date = '';
		if (!$ar->media_copyright) $ar->media_copyright = '';
		if (!$ar->media_description) $ar->media_description = '';

		$ar->__jsonMedia = $mediaBridge->jsonFromModel($ar);

        $application = $this->application;
        $user = $application->_user;
		$ar->__url__ =  $user->acl($application->getPageId(),'edit') ? __Routing::makeUrl('actionsMVC', array('action' => 'edit', 'id' => $ar->media_id)) : false;
		$ar->__urlDelete__ = $user->acl($application->getPageId(),'delete') ? __Routing::makeUrl('actionsMVC', array('action' => 'delete', 'id' => $ar->media_id)) : false;
		$ar->__urlDownload__ = pinaxcms_helpers_Media::getFileUrlById($ar->media_id);
		$ar->__urlPreview__ = $ar->media_type === 'IMAGE' ? pinaxcms_helpers_Media::getImageUrlById($ar->media_id, 800, 600) : pinaxcms_helpers_Media::getFileUrlById($ar->media_id);
	}
}
