<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_ajax_SaveDraftClose extends pinaxcms_contents_controllers_moduleEdit_ajax_SaveDraft
{
    function execute($data)
    {
        $this->checkPermissionForBackend();
        $result = parent::execute($data, null);

        if ($result['errorFields']) {
            return $result;
        }

        return array('url' => $this->changeAction(''));
    }
}
