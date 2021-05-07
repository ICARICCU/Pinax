<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_authentication_AbstractLogin extends PinaxObject implements pinax_authentication_AuthenticationDriver
{
    protected $loginId;
    protected $language = null;
    protected $psw;
    protected $arUser;
    protected $allowGroups = array();
    protected $onlyBackendUser = false;

    /**
     * @var pinax_authentication_CookieEncryptedBag
     */
    protected $cookie;


    function __construct()
	{
		$this->cookie = new pinax_authentication_CookieEncryptedBag(
                            new pinax_encryption_Encrypter(__Paths::get('APPLICATION_CONFIG').'secret-key.txt')
                        );
    }


    /**
     * @return void
     */
    public function loginFromRequest($loginIdField, $passwordFields, $rememberField=false, $readFromCookie=false)
    {
        $loginId   = trim(__Request::get($loginIdField, $readFromCookie ? @$_COOKIE['pinax_username'] : '' ));
        $psw       = trim(__Request::get($passwordFields, $readFromCookie ? @$_COOKIE['pinax_password'] : ''));
        $remember  = __Request::get($rememberField, 0);
        $this->login($loginId, pinax_password($psw), $remember);
    }

    /**
     * @return bool
     */
    public function loginFromCookie()
    {
        $authData = $this->cookie->load();

        try {
            if ($authData->isValid()) {
                $this->login($authData->loginId(), $authData->password());
                return true;
            }
        } catch(pinax_authentication_AuthenticationException $e) {}

        $this->cookie->reset();
        return false;
    }


    /**
     * @return void
     */
    public function setAllowGroups($allowGroups)
    {
        $this->allowGroups = $allowGroups;
    }

    /**
     * @return void
     */
    public function setOnlyBackendUser($onlyBackendUser)
    {
        $this->onlyBackendUser = $onlyBackendUser;
    }

    /**
     * @return void
     */
    public function setUserLanguage($language)
    {
        $this->language = $language;
    }

	/**
	 * @param string $loginId
	 * @param string $psw
	 *
	 * @return void
	 */
	protected function validateLogin($loginId, $psw)
    {
        if (!$loginId || !$psw) {
            throw pinax_authentication_AuthenticationException::emptyLoginIdOrPassword();
        }
    }

    /**
     * @return void
     */
    protected function resetSession() {
        __Session::set('pinax.userLogged', false);
        __Session::set('pinax.user', NULL);
    }

    /**
     * @param (mixed|null|true)[] $user
     *
     * @return void
     */
    protected function setSession($user) {
        __Session::set('pinax.userLogged', true);
        __Session::set('pinax.user', $user);
    }

    /**
     * @return void
     */
    protected function resetCookie() {
        $this->cookie->reset();
    }

    /**
     * @return void
     */
    protected function setCookie($loginId, $psw) {
        $this->cookie->save($loginId, $psw);
    }

    /**
	 * @param string $loginId
	 * @param string $password
	 * @param boolean $remember
	 * @return array
	 */
	abstract public function login($loginId, $password, $remember=false);

	/**
	 * @return void
	 */
	abstract public function logout();
}
