<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_render_RenderCellRecordSetList extends PinaxObject
{
	protected $application;

	function __construct(&$application)
	{
		$this->application = $application;
	}

	function renderCell( &$ar, $params )
	{
	}
}
