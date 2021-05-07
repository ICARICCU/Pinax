<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_renderer_CellUser extends pinax_components_render_RenderCell
{
    function renderCell($key, $value, $item, $columnName)
    {
        $ar = pinax_ObjectFactory::createModel('pinax.models.User');
        $ar->load($value);
        return $ar->user_firstName.' '.$ar->user_lastName;
    }
}
