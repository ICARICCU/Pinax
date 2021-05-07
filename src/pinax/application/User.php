<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_application_User extends PinaxObject
{

    var $id;
    var $firstName;
    var $lastName;
    var $loginId;
    var $email;
    var $groupId;

    /**
     * @var bool
     */
    var $backEndAccess;
    var $language;
    var $active;
    var $dateCreation;

    /**
     * @var null|object
     */
    var $_acl = null;
    private $properties;

    function __construct($userInfo)
    {
        if (is_object($userInfo)) {
            $userInfo = (array)$userInfo;
        }
        $this->id            = $userInfo['id'];
        $this->firstName     = $userInfo['firstName'];
        $this->lastName      = $userInfo['lastName'];
        $this->loginId       = $userInfo['loginId'];
        $this->email         = $userInfo['email'];
        $this->groupId       = $userInfo['groupId'];
        $this->backEndAccess = $userInfo['backEndAccess']==1;
        $this->language      = isset($userInfo['language']) ? $userInfo['language'] : '';
        $this->active        = isset($userInfo['isActive']) ? $userInfo['isActive'] : false;
        $this->dateCreation  = isset($userInfo['dateCreation']) ? $userInfo['dateCreation'] : '';
        $this->properties    = isset($userInfo['properties']) ? $userInfo['properties'] : array();

        // TODO gestire __Config::get('ACL_ENABLED')
        // creando un UserAcl che viene creato se __Config::get('ACL_ENABLED') = true
        $this->_acl = pinax_ObjectFactory::createObject(__Config::get('ACL_CLASS'), $this->id, $this->groupId);

        pinax_ObjectValues::set('org.pinax', 'userId', $this->id);
    }

    function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    function toString()
    {
        return "user: " . $this->id . ", " . $this->loginId . ", " . $this->firstName . " " . $this->lastName;
    }

    /**
     * @return bool
     */
    function isLogged()
    {
        return $this->id <> 0;
    }

    /**
     * @param string $action
     * @param bool|null $default
     * @param string $name
     */
    function acl($name, $action, $default = null, $options = null)
    {
        return $this->_acl->acl($name, $action, $default, $options);
    }

    function checkActions($action, $role)
    {
        return $this->_acl->checkActions($action, $role);
    }

    function isInRole($roleId)
    {
        return $this->_acl->inRole($roleId);
    }

    function isInRoles($roles)
    {
        return $this->_acl->isInRoles($roles);
    }

    function getRoles()
    {
        return $this->_acl->getRoles();
    }

    function invalidateAcl()
    {
        return $this->_acl->invalidateAcl();
    }

    function isActive()
    {
        return $this->active;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    function getProperty($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }


    /**
     * @return mixed
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
}
