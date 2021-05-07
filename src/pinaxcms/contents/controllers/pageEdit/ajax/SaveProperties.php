<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_SaveProperties extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $data = json_decode($data);

        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        $menu->load($data->menu_id);
        $menu->menu_isLocked = $data->menu_isLocked;
        $menu->menu_hasComment = $data->menu_hasComment;
        $menu->menu_printPdf = $data->menu_printPdf;
        $menu->menu_pageType = $data->menu_pageType;
        $menu->menu_cssClass = $data->menu_cssClass;
        if (@$data->menu_creationDate) $menu->menu_creationDate = $data->menu_creationDate;
        $menu->save();


        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.MenuDetail');
        $menu->find(array('menudetail_FK_menu_id' => $data->menu_id, 'menudetail_FK_language_id' => pinax_ObjectValues::get('org.pinax', 'editingLanguageId')));
        $menu->menudetail_url = $data->menudetail_url;
        $menu->menudetail_title = $data->menudetail_title;
        $menu->menudetail_titleLink = $data->menudetail_titleLink;
        $menu->menudetail_linkDescription = $data->menudetail_linkDescription;
        $menu->menudetail_seoTitle = $data->menudetail_seoTitle;
        $menu->menudetail_keywords = $data->menudetail_keywords;
        $menu->menudetail_description = $data->menudetail_description;
        $menu->menudetail_subject = $data->menudetail_subject;
        $menu->menudetail_creator = $data->menudetail_creator;
        $menu->menudetail_publisher = $data->menudetail_publisher;
        $menu->menudetail_contributor = $data->menudetail_contributor;
        $menu->menudetail_type = $data->menudetail_type;
        $menu->menudetail_identifier = $data->menudetail_identifier;
        $menu->menudetail_source = $data->menudetail_source;
        $menu->menudetail_relation = $data->menudetail_relation;
        $menu->menudetail_coverage = $data->menudetail_coverage;
        if ($menu->fieldExists('menudetail_hideInNavigation')) {
            $menu->menudetail_hideInNavigation = $data->menudetail_hideInNavigation;
        }
        $menu->save();

        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $menuProxy->invalidateSitemapCache();

        return true;
    }
}
