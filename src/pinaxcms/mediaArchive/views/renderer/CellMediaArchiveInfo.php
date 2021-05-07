<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_views_renderer_CellMediaArchiveInfo extends PinaxObject
{
	function renderCell( $key, $value, $row )
	{
		$output = '';
		$output .= '<p>'.__T('PNX_MEDIA_TITLE').': <strong>'.( !empty( $row[ 'media_title' ] ) ? $row[ 'media_title' ] : '-').'</strong><br />';
		$output .= __T('PNX_MEDIA_CATEGORY').': <strong>'.( !empty( $row[ 'media_category' ] ) ? $row[ 'media_category' ] : '-').'</strong><br />';
		$output .= __T('PNX_MEDIA_FILENAME').': <strong>'.$row[ 'media_originalFileName' ].'</strong> <small>('.$row[ 'media_fileName' ].')</small><br />';
		$output .= __T('PNX_MEDIA_SIZE').': <strong>'.number_format( $row[ 'media_size' ] /1024).' Kb</strong></p>';
		return $output;
	}
}
