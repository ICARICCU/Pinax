<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_GetChildBlocks extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($id)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;
        $output = array();

        $languageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ContentProxy');

        $itMenus = $menuProxy->getChildMenusFromId($id, $languageId, false);
        foreach($itMenus as $subMenu) {
            if ($subMenu->menu_type!=='BLOCK') continue;
            $content = $contentProxy->readRawContentFromMenu($subMenu->menu_id, $languageId, 'PUBLISHED');
            $description = '';
            if ($content) {
                $description = property_exists($content->content, 'text') ? $content->content->text :
                                (property_exists($content->content, 'description') ? $content->content->description : '');
            }

             $output[] = array(
                    'id' => $subMenu->menu_id,
                    'title' => $subMenu->menudetail_title,
                    'visible' => $subMenu->menudetail_isVisible,
                    'description' => pinax_strtrim($description).' ('.__T($subMenu->menu_pageType).')'
                );

        }

        return $output;
    }
}
