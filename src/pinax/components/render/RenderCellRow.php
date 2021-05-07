<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_render_RenderCellRow extends PinaxObject
{
	var $application;

	function __construct(&$application)
	{
		$this->application = $application;
	}

	function renderRow( $item, $cssClass )
	{
		return '';
	}



}
