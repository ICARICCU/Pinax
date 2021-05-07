<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_session_controllers_SessionCheck extends pinax_mvc_core_CommandAjax
{
    public function execute()
    {
        $currentUser = $this->application->getCurrentUser();
        return $currentUser->id ?
            [ 'status' => true ] :
            [ 'status' => false,
              'message' => __T('Session timeout waring') ,
              'url' => (__Config::get('pinaxcms.session.check.return.url')) ? : PNX_HOST
            ];
    }
}
