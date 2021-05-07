<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_FilterValue extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('name',			true, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',			true, 	'',		COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function getItem()
	{
		return array($this->getAttribute('name') => $this->getAttribute('value'));
	}
}
