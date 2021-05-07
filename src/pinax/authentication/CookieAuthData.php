<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_authentication_CookieAuthData
{
    private $authData;

    function __construct($authData)
	{
		$this->authData = $authData;
    }


    /**
     * @return boolean
     */
    public function isValid()
    {
        return is_array($this->authData) && isset($this->authData['loginId']) && isset($this->authData['password']);
    }

    /**
     * @return string
     */
    public function loginId()
    {
        return $this->authData['loginId'];
    }

    /**
     * @return string
     */
    public function password()
    {
        return $this->authData['password'];
    }
}
