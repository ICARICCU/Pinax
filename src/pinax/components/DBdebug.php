<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_DBdebug extends pinax_components_Component
{

	function init()
	{
		// define the custom attributes
		$this->defineAttribute('value', false, true, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('connect', false, 0, COMPONENT_TYPE_INTEGER);

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		pinax_import('pinax.dataAccess.DataAccess');
		pinax_DBdebug( $this->getAttribute('value'), $this->getAttribute('connect') );
	}

}
