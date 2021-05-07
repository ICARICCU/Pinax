<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_fe_controllers_login_Login extends pinax_mvc_core_Command
{
    public function execute()
    {
        $c = $this->view->getComponentById('formLoginPage');
        if (is_object($c)) {
            $url = $this->view->loadContent('loginPage');
            $speakingUrlManager = $this->application->retrieveProxy('pinaxcms.speakingUrl.Manager');
            $c->setAttribute('accessPageId', $speakingUrlManager->makeUrl($url));
        }
    }
}
