<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_activeRecordEdit_ajax_Cancel extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute()
    {
        $this->checkPermissionForBackend();

        $this->directOutput = true;
        return array('url' => $this->changeAction(''));
    }
}
