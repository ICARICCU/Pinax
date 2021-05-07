<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_ajax_SavePublish extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $data = json_decode($data);
        $pageId = $this->application->getPageId();
        if (!$this->user->acl($pageId, 'visible', null, $data->__id) || !$this->user->acl($pageId, 'edit', null, $data->__id)) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $this->directOutput = true;
        $result = $this->save($data, false, true);

        if ($result['errorFields']) {
            return $result;
        }

        return array('url' => __Request::get('statusReleadUrl').pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED);
    }
}
