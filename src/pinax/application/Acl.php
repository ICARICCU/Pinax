<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_application_Acl extends PinaxObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $groupId;

    /**
     * @var array
     */
    protected $_acl;

    /**
     * @var pinax_application_Application
     */
    protected $application;

    function __construct( $id, $groupId)
    {
        $this->id = $id;
        $this->groupId = $groupId;

        $this->application = pinax_ObjectValues::get('org.pinax', 'application');
        $fileName = pinax_Paths::getRealPath('APPLICATION', 'config/acl.xml');
        $compiler = pinax_ObjectFactory::createObject('pinax.compilers.Acl');
        $compiledFileName = $compiler->verify($fileName);

        $this->_acl = include($compiledFileName);
    }

    /**
     * @param string $name
     * @param string $action
     * @param boolean $default
     * @param mixed $options
     * @return boolean
     */
    public function acl($name, $action, $default=NULL, $options=null)
    {
        if ($this->id==0) return false;

        $action = strtolower($action);
        $name   = $name=='*' ? strtolower($this->application->getPageId()) : strtolower($name);
        if (isset($this->_acl[$name]))
        {
            if (isset($this->_acl[$name]['rules'][$action]))
            {
                $rules = $this->_acl[$name]['rules'][$action];
                return in_array($this->groupId, $rules['allowGroups']) || in_array('*', $rules['allowGroups']) ?
                                        true
                                        :
                                        in_array($this->id, $rules['allowUsers']) ? true : false;
            }
            else
            {
                return is_null($default) ? $this->_acl[$name]['default'] : $default;
            }
        }
        else
        {
            return is_null($default) ? true : $default;
        }
    }

    /**
     * @param string $action
     * @param string $role
     * @return boolean
     */
    public function checkActions($action, $role)
    {
        for ($i=0; $i<count($role); $i++)
        {
            $action = $action==$role[$i][1] ? ($this->acl($role[$i][0], $role[$i][1]) ? $role[$i][2] : $role[$i][3]) : $action;
        }
        return $action;
    }

    /**
     * @return void
     */
    public function invalidateAcl()
    {
        $fileName = pinax_Paths::getRealPath('APPLICATION', 'config/acl.xml');
        $compiler = pinax_ObjectFactory::createObject('pinax.compilers.Acl');
        $compiler->invalidate($fileName);
    }

    /**
     * @param array $roles
     * @return boolean
     */
    public function isInRoles($roles)
    {
        return false;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return [];
    }
}
