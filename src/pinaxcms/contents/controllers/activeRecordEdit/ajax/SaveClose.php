<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_activeRecordEdit_ajax_SaveClose extends pinaxcms_contents_controllers_activeRecordEdit_ajax_Save
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($data)
    {
        $this->checkPermissionForBackend();
        $result = parent::execute($data);

        if ($result['errorFields']) {
            return $result;
        }

        return array('url' => $this->changeAction(''));
    }
}
