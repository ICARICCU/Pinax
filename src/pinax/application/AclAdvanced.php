<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_application_AclAdvanced extends pinax_application_Acl
{
    /**
     * @var array
     */
    protected $roles;

    /**
     * @var array
     */
    protected $aclMatrix;

    /**
     * @var bool
     */
    protected $debugBackdoor;

    /**
     * @param string $id
     * @param string $groupId
     */
    function __construct($id, $groupId)
    {
        parent::__construct($id, $groupId);

        $this->debugBackdoor = __Config::get('ACL_DEBUG_BACKDOOR')===true;
        $this->roles = array();
        $this->aclMatrix = array();

        if ($id)  {
            // TODO ora la matrice è memorizzata nella sessione
            // e non può essere invalidata dal gestore dei ruoli per tutti gli utenti
            $roles = __Session::exists('pinax.roles');
            if (!empty($roles)) {
                $this->roles = __Session::get('pinax.roles');
                $this->aclMatrix = __Session::get('pinax.aclMatrix');
            } else {
                $it = pinax_ObjectFactory::createModelIterator('pinax.models.Role', 'getPermissions', array('params' => array('id' => $id, 'groupId' => $groupId)));

                foreach ($it as $ar) {
                    // se il ruolo non è attivo passa al prossimo
                    if (!$ar->role_active) continue;

                    // se il ruolo non è stato ancora processato
                    if (!$this->roles[$ar->role_id]) {
                        $this->roles[$ar->role_id] = true;
                        $permissions = unserialize($ar->role_permissions);
                        // unione delle matrici dei permessi
                        foreach ($permissions as $name => $actions) {
                            foreach ((array)$actions as $action => $value) {
                                $this->aclMatrix[strtolower($name)][$action] |= $value;
                            }
                        }
                    }
                }

                __Session::set('pinax.roles', $this->roles);
                __Session::set('pinax.aclMatrix', $this->aclMatrix);
            }
        }
    }

    /**
     * @param string $name
     * @param string $action
     * @param boolean $default
     * @param mixed $options
     * @return boolean
     */
    public function acl($name, $action, $default=null, $options=null)
    {
        if ($this->debugBackdoor) return $this->debugBackdoor;

        $name = $name=='*' ? strtolower($this->application->getPageId()) : strtolower($name);
        if (isset($this->aclMatrix[$name])) {
            return $this->aclMatrix[$name]['all'] || $this->aclMatrix[$name][$action];
        } else {
            return is_null($default) ? false : $default;
        }
    }

    /**
     * @param string $roleId
     * @return string
     */
    public function inRole($roleId)
    {
        return $this->roles[$roleId];
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return array_keys($this->roles);
    }

    /**
     * @return void
     */
    public function invalidateAcl()
    {
    }

    /**
     * @param array $roles
     * @return boolean
     */
    public function isInRoles($roles)
    {
        foreach ($roles as $roleId) {
            if ($this->inRole($roleId)) {
                return true;
            }
        }

        return false;
    }
}
