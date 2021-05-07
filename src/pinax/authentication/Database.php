<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_authentication_Database extends pinax_authentication_AbstractLogin
{
	/**
	 * @param string $loginId
	 * @param string $password
	 * @param boolean $remember
	 * @return array
	 */
	public function login($loginId, $password, $remember=false)
    {
        $this->validateLogin($loginId, $password);
        $this->resetSession();

        $tempArUser = pinax_ObjectFactory::createModel('pinax.models.User');
        $arUser = pinax_ObjectFactory::createModelIterator('pinax.models.User')
            ->load('login', array('loginId' => $loginId, 'password' => $password))
            ->first();

        if (!$arUser && $tempArUser->fieldExists('user_passwordTemp')) {
            $arUser = pinax_ObjectFactory::createModelIterator('pinax.models.User')
            ->load('loginTempPassword', array('loginId' => $loginId, 'password' => $password))
            ->first();

            if ($arUser) {
                $arUser->user_password = $arUser->user_passwordTemp;
                $arUser->user_passwordTemp = '';
                $arUser->save();
            }
        }

        if ($arUser) {
            return $this->logUser($arUser, $remember);
        }

        throw pinax_authentication_AuthenticationException::wrongLoginIdOrPassword();
    }

    /**
     * @param pinax_dataAccessDoctrine_AbstractActiveRecord $arUser
     * @param boolean $remember
     * @return array
     */
    protected function logUser($arUser, $remember=false)
    {
        if ($arUser->user_isActive==0) {
            throw pinax_authentication_AuthenticationException::userNotActive();
        }

        $language = $this->language;
        if (!$language) $language = __Config::get('DEFAULT_LANGUAGE');

        $userInfo = array(
            'id' => $arUser->user_id,
            'firstName' => $arUser->user_firstName,
            'lastName' => $arUser->user_lastName,
            'loginId' => $arUser->user_loginId,
            'email' => $arUser->user_email,
            'groupId' => $arUser->user_FK_usergroup_id,
            'backEndAccess' => false,
            'language' => $language,
            'dateCreation' => $arUser->user_dateCreation
        );


        if (__Config::get('ACL_ROLES') && $this->onlyBackendUser) {
            $user = pinax_ObjectFactory::createObject('pinax.application.User', $userInfo);

            if (!$user->acl('Home', 'all')) {
                __Session::destroy();
                throw pinax_authentication_AuthenticationException::AccessNotAllowed();
            }

            $userInfo['backEndAccess'] = true;
        } else {
            if ($this->onlyBackendUser && $arUser->usergroup_backEndAccess==0) {
                throw pinax_authentication_AuthenticationException::AccessNotAllowed();
            }

            if (count($this->allowGroups) ? !in_array($arUser->user_FK_usergroup_id, $this->allowGroups) : false) {
                throw pinax_authentication_AuthenticationException::AccessNotAllowed();
            }

            $userInfo['backEndAccess'] = $arUser->usergroup_backEndAccess;
        }

        $this->setSession($userInfo);

        if ($remember) {
            $this->setCookie($arUser->user_loginId, $arUser->user_password);
        }

        $evt = array('type' => PNX_EVT_USERLOGIN, 'data' => $userInfo);
        $this->dispatchEvent($evt);
        return $userInfo;
    }

    /**
     * @return void
     */
    public function logout()
    {
        $evt = array('type' => PNX_EVT_USERLOGOUT, 'data' => '');
        $this->dispatchEvent($evt);

        if (__Config::get('USER_LOG')) {
            $user = __Session::get('pinax.user');
            $arLog = &pinax_ObjectFactory::createModel('pinax.models.UserLog');
            $arLog->load($user['logId']);
            $arLog->delete();
        }

        __Session::removeAll();
        $this->resetCookie();
    }
}
