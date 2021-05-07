<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_authentication_AuthenticationDriver
{
	/**
	 * @param string $loginId
	 * @param string $password
	 * @param boolean $remember
	 * @return array
	 */
	public function login($loginId, $password, $remember=false);

	/**
	 * @return void
	 */
	public function logout();
}
