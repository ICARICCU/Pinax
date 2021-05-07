<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Logout extends pinax_components_Component
{
	var $_error = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function process()
	{
		$authClass = pinax_ObjectFactory::createObject(__Config::get('pinax.authentication'));
		if ($authClass) {
			$authClass->logout();
		}

		pinax_helpers_Navigation::gotoUrl( PNX_HOST );
		exit();
	}
}
