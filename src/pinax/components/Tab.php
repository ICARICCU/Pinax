<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Tab extends pinax_components_State
{
	var $_state;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('url', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('draw', false , true, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('onlyLabel', false , false, COMPONENT_TYPE_BOOLEAN);
		// call the superclass for validate the attributes
		parent::init();
	}
}
