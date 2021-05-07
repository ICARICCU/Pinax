<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_views_renderer_DocumentTitle extends pinax_components_render_RenderCell
{
    function renderCell( $key, $value, $row, $columnName )
    {
        $value = pinax_encodeOutput($value);
        if ($row->isTranslated()) {
            return $value;
        }
        else {
            return '<em>'.$value.'</em>';
        }
    }
}
