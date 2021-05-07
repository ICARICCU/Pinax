<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_authentication_CookieEncryptedBag
{
    const COOKIE_NAME = 'pinax_auth';

    /**
     * @var pinax_interfaces_Encrypter
     */
    private $encrypter;

    function __construct(pinax_interfaces_Encrypter $encrypter)
	{
		$this->encrypter = $encrypter;
    }

    /**
     * @param string $loginId
     * @param string $password
     * @return void
     */
    public function save($loginId, $password)
    {
        $lifetime = time() + 60*60*24*30;
        $authData = ['loginId' => $loginId, 'password' => $password];

        setcookie(self::COOKIE_NAME, $this->encrypter->encrypt($authData), $lifetime, '/');
    }

    /**
     * @return pinax_authentication_CookieAuthData
     */
    public function load()
    {
        $authData = @$_COOKIE[self::COOKIE_NAME];
        try {
            if ($authData) {
                $authData = $this->encrypter->decrypt($authData);
            }
        } catch (Exception $e) {
            $this->reset();
        }

        return new pinax_authentication_CookieAuthData($authData);;
    }

    /**
     * @return void
     */
    public function reset()
    {
        setcookie(self::COOKIE_NAME, '', time()-3600, '/');
    }
}
