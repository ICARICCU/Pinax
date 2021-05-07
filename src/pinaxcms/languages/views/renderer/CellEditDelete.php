<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_languages_views_renderer_CellEditDelete extends pinax_components_render_RenderCell
{
    function renderCell($key, $value, $row, $columnName)
	{
        $pageId = $this->application->getPageId();
        if ($this->user->acl($pageId, 'all') or $this->user->acl($pageId, 'edit')) {
            $output = pinax_Assets::makeLinkWithIcon(  'actionsMVC',
                                                        'icon-pencil btn-icon',
                                                        array(
                                                            'title' => __T('PNX_RECORD_EDIT'),
                                                            'id' => $key,
                                                            'action' => 'edit'));
        }

        if (!$row->language_isDefault) {
            $output .= pinax_Assets::makeLinkWithIcon(   'actionsMVC',
                                                            'icon-trash btn-icon',
                                                            array(
                                                                'title' => __T('PNX_RECORD_DELETE'),
                                                                'id' => $key,
                                                                'action' => 'delete'),
                                                            __T( 'Sei sicuro di voler cancellare il record?'));
        }

		return $output;
	}
}
