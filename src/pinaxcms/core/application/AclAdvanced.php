<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_core_application_AclAdvanced extends pinax_application_AclAdvanced
{
    private $documentAclMap = [];

    public function acl($name, $action, $default=NULL, $options=null)
    {
        $id = $options;
        $result = parent::acl($name, $action, $default);
        if (!$id) return $result;

        if ($result && in_array($action, ['edit', 'delete', 'visible', 'publish', 'new'])) {
            return $this->checkRolesInEdit($id);
        } else if ($result && in_array($action, ['visible-fe'])) {
            return $this->checkRolesInView($id);
        }

        return $result;
    }

    /**
     * @param int $id
     * @return boolean
     */
    private function checkRolesInEdit($id)
    {
        return $this->checkRolesIn($id, '__aclEdit');
    }

        /**
     * @param int $id
     * @return boolean
     */
    private function checkRolesInView($id)
    {
        return $this->checkRolesIn($id, '__aclView');
    }

    /**
     * @param int $id
     * @param string $key
     * @return boolean
     */
    private function checkRolesIn($id, $key)
    {
        $ar = $this->getDocumentAcl($id);
        if (!$ar->{$key}) return true;

        $roles = explode(',', $ar->{$key});
        return $this->isInRoles($roles);
    }

    /**
     * @param int $id
     * @return pinaxcms_contents_models_DocumentACL
     */
    private function getDocumentAcl($id)
    {
        if (isset($this->documentAclMap[$id])) {
            return $this->documentAclMap[$id];
        }

        $ar = pinax_ObjectFactory::createModel('pinaxcms.contents.models.DocumentACL');
        $ar->load($id);
        $this->documentAclMap[$id] = $ar;

        return $ar;
    }
}
