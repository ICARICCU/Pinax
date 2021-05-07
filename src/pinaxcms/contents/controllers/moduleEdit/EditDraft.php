<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_EditDraft extends pinaxcms_contents_controllers_moduleEdit_Edit
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    /**
     * @param int $id
     * @return void
     */
    public function execute($id)
    {
        $this->checkPermissionForBackend();

        $pageId = $this->application->getPageId();
        if (!$this->user->acl($pageId, 'visible', null, $id) || !$this->user->acl($pageId, 'editDraft', null, $id)) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $this->setViewContent($id, 'DRAFT');
    }
}
