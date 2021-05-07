<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_Add extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;
        $data = json_decode($data);
        if ($data) {
// TODO: controllo acl
            $pageTitle = $data->title;
            $pageParent = $data->pageParent;
            $pageType = $data->pageType;
            if ($pageTitle && $pageParent && $pageType) {
                $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
                $pageId = $menuProxy->addMenu(  $pageTitle,
                                                $pageParent,
                                                $pageType,
                                                strtolower(__Request::get('action'))=='addblock' ? pinaxcms_core_models_enum_MenuEnum::BLOCK : pinaxcms_core_models_enum_MenuEnum::PAGE
                                                );
                return array(
                            'evt' => 'pinaxcms.pageAdded',
                            'message' => array('menuId' => $pageId, 'parentId' => $pageParent)
                        );
            }
        }
        return false;
    }
}
