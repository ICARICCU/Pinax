<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class  pinax_components_StateSwitchClass extends PinaxObject
{
	var $_parent = NULL;

	function __construct(&$parent)
	{
		$this->_parent = &$parent;
	}
}
