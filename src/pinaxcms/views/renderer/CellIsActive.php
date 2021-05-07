<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_renderer_CellIsActive extends pinax_components_render_RenderCell
{
	function renderCell($key, $value, $item, $columnName)
	{
		if ($value=='1' || $value===true) $value = '<span class="'.__Config::get('pinax.datagrid.checkbox.on').'"></span>';
		else $value = '<span class="'.__Config::get('pinax.datagrid.checkbox.off').'"></span>';
		return $value;
	}
}
