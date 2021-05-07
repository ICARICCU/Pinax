<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_tinymce_ajax_LinkList extends pinax_mvc_core_CommandAjax
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute()
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;
        $links = array('internal' => array());

        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $siteMap = $menuProxy->getSiteMap();
        $siteMap->getSiteArray();
        $siteMapIterator = &pinax_ObjectFactory::createObject('pinax.application.SiteMapIterator', $siteMap);
        while (!$siteMapIterator->EOF)
        {
            $n = $siteMapIterator->getNodeArray();
            if ($n['type'] != pinaxcms_core_models_enum_MenuEnum::BLOCK ) {
                $links['internal'][] = array(   'name' => str_repeat('.  ', $n["depth"]-1).strip_tags($n['title']),
                                                'link' => 'internal:'.$n['id']);
            }
            $siteMapIterator->moveNext();
        }
        return $links;
    }
}
