<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_ajax_SaveDraft extends pinaxcms_contents_controllers_moduleEdit_ajax_Save
{
    public function execute($data, $status='')
    {
        $this->checkPermissionForBackend();

        $data = json_decode($data);
        $pageId = $this->application->getPageId();
        if (!$this->user->acl($pageId, 'visible', null, $data->__id) || !$this->user->acl($pageId, 'editDraft', null, $data->__id)) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $this->directOutput = true;
        $reload = $status && $status!=pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT;
        $result = $this->save($data, true);

        if ($result['errorFields']) {
            return $result;
        }

        if ($reload) {
            return array('url' => __Request::get('statusReleadUrl').pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT);
        }

        return $result;
    }
}

