<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_render_RenderCell extends PinaxObject
{
	protected $application;
	protected $user = NULL;

	function __construct($application)
	{
		$this->application = $application;
		$this->user = $this->application->getCurrentUser();
	}

	function renderCell($key, $value, $row, $columnName)
	{
		return '';
	}

	function getHeader( $text )
	{
		return $text;
	}

	function getCssClass( $key, $value, $item )
	{
		return '';
	}

}
