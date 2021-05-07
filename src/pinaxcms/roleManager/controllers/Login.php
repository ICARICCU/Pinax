<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_roleManager_controllers_Login extends pinax_mvc_core_Command
{
    function execute()
    {
        if ($this->user->isLogged()) {
            $siteMap = $this->application->getSiteMap();
            $siteMapIterator = pinax_ObjectFactory::createObject('pinax.application.SiteMapIterator',$siteMap);
            while (!$siteMapIterator->EOF)
            {
                $n = $siteMapIterator->getNode();
                $siteMapIterator->moveNext();
                if ($n->isVisible && $n->depth > 1 && !$n->select) {
                    pinax_helpers_Navigation::gotoUrl(pinax_helpers_Link::makeUrl('link', array('pageId' => $n->id)));
                }
            }

            $authClass = pinax_ObjectFactory::createObject(__Config::get('pinax.authentication'));
            $authClass->logout();
        }
    }
}
