<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Module extends pinax_components_ComponentContainer
{

	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
	}

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->canHaveChilds = true;
		$this->overrideEditableRegion = false;

		// define the custom attributes
		$this->defineAttribute('adm:editComponents',	false, array(), 	COMPONENT_TYPE_ENUM);

		// call the superclass for validate the attributes
		parent::init();
	}
}
