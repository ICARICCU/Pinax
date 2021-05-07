<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_models_proxy_MenuProxy extends PinaxObject
{
    /**
     * Load the site map from DB
     *
     * @return pinaxcms.core.application.SiteMapDB The sitemap
     */
    public function getSiteMap($load=true)
    {
        $siteMap = pinax_ObjectFactory::createObject('pinaxcms.core.application.SiteMapDB');
        if ($load) {
            $siteMap->loadTree();
        }
        return $siteMap;
    }

    /**
     * Get a menu root menu record
     *
     * @return pinax.dataAccessDoctrine.ActiveRecord  The menu record
     */
    public function getRootMenu($languageId)
    {
        // pinax_dataAccessDoctrine_DataAccess::enableLogging();
        $menu = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu')
            ->load('getRootMenu', array('languageId' => $languageId))
            ->first();

        return $menu;
    }

    /**
     * Get a menu record from id
     *
     * @return pinax.dataAccessDoctrine.ActiveRecord  The menu record
     */
    public function getMenuFromId($menuId, $languageId)
    {
        // pinax_dataAccessDoctrine_DataAccess::enableLogging();
        $menu = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu')
            ->load('getMenu', array('menuId' => $menuId, 'languageId' => $languageId))
            ->first();

// TODO: lanciare un'eccezione se il menù non è trovato
        return $menu;
    }


    /**
     * Get child menus from id
     *
     * @return pinax.dataAccessDoctrine.RecordIterator
     */
    public function getChildMenusFromId($menuId, $languageId, $skipBlock=true)
    {
        $menus = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu')
            ->load('getChildMenus', array('menuId' => $menuId, 'languageId' => $languageId, 'skipBlock' => $skipBlock));

// TODO: lanciare un'eccezione se il menù non è trovato
        return $menus;
    }


    /**
     * Move a menu in a new position
     */
    public function moveMenu($menuId, $position, $parentId)
    {
        if (!$parentId) return;

        // load the menu from DB
        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        $menu->load($menuId);
        $isBlock = $menu->menu_type == 'BLOCK';

        // set the parent and the order
// TODO: lanciare un'eccezione se il menù non è trovato
        $menu->menu_parentId = $parentId;
        $menu->menu_order = $position;
        $menu->save();

        // reorder the children menus
        $menus = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu');
        $menus->load('getChildrenMenuInOrder', array('menuId' => $parentId, 'isBlock' => $isBlock));
// TODO: lanciare un'eccezione se il parent trovato
        $pos = 0;
        foreach ($menus as $menu) {
            if ($menuId == $menu->menu_id) continue;
            if ($position == $pos) $pos++;
            $menu->menu_order = $pos;
            $menu->save();
            $pos++;
        }
        $this->invalidateSitemapCache();

        $evt = array('type' => pinaxcms_contents_events_Menu::MOVE, 'data' => $menuId);
        $this->dispatchEvent($evt);
    }

    /**
     * Delete a menu
     * @param  int $menuId the menu id
     */
    public function deleteMenu($menuId)
    {
        // cancella prima i nodi figli
        $menus = $this->getChildMenusFromId($menuId, pinax_ObjectValues::get('org.pinax', 'languageId'), false);
        foreach ($menus as $ar) {
            $this->deleteMenu($ar->menu_id);
        }
        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        $menu->delete($menuId);

        $contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ContentProxy');
        $contentProxy->deleteContent($menuId);
        $this->invalidateSitemapCache();

        $evt = array('type' => pinaxcms_contents_events_Menu::DELETE, 'data' => $menuId);
        $this->dispatchEvent($evt);
    }

    /**
     * Add a new menu
     *
     * @param string $title    the menu title
     * @param int $parent      the menu parent id
     * @param string $pageType the menu pageType
     * @return int             new menu id
     */
    public function addMenu($title, $parent, $pageType, $type='PAGE') {
        $userId = pinax_ObjectValues::get('org.pinax', 'userId');

        $menus = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu');
        $menus->load('getChildrenMenuInOrder', array('params' => array('menuId' => $parent, 'isBlock' => $type!='PAGE')));
        $order = $menus->count()+1;

        $application = pinax_ObjectValues::get('org.pinax', 'application');
		$menudetail_isVisible = 1;
        $user = $application->getCurrentUser();
        if ($user) {
            $menudetail_isVisible = $user->acl(__Config::get('SITEMAP_ID'), 'publish') === true ? 1 : 0;
        }

        // add the menu
        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        $ar->menu_parentId  = $parent;
        $ar->menu_pageType  = $pageType;
        $ar->menu_order     = $order;
        $ar->menu_type      = $type;
        $ar->menu_hasPreview= '1';
        $ar->menu_creationDate = new pinax_types_DateTime();
        $ar->menu_modificationDate = new pinax_types_DateTime();
        if (!__Config::get('MULTISITE_ENABLED')) {
            $ar->menu_FK_site_id = 0;
        }

        $ar->menudetail_title = $title;
        $ar->menudetail_isVisible = $menudetail_isVisible;
        $ar->menudetail_url = '';
        $ar->menudetail_seoTitle = '';
        $ar->menudetail_description = '';
        $ar->menudetail_subject = '';
        $ar->menudetail_keywords = '';
        $ar->menudetail_creator = '';
        $ar->menudetail_publisher = '';
        $ar->menudetail_contributor = '';
        $ar->menudetail_type = '';
        $ar->menudetail_identifier = '';
        $ar->menudetail_source = '';
        $ar->menudetail_relation = '';
        $ar->menudetail_coverage = '';

        $pageId = $ar->save();

        // reorder all brothers menus
        $this->moveMenu($pageId, $order, $parent);

        $this->invalidateSitemapCache();
        $evt = array('type' => pinaxcms_contents_events_Menu::ADD, 'data' => $pageId);
        $this->dispatchEvent($evt);

        return $pageId;
    }


    /**
     * Duplicate a menu entry only of the language given
     *
     * @param int $menuId      the menu id to copy
     * @param int $languageId  the main languague id
     * @param int $parentId    the parentId of menu (optional)
     * @return int             new menu id
     */
    public function duplicateMenuEntry($menuId, $languageId, $parentId = null, $duplicateId = null)
    {
        $menu = $this->getMenuFromId($menuId, $languageId);

        $newMenu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        if ($duplicateId) {
            $newMenu->load($duplicateId);
            $newMenu->setDetailId(null);
        }

        foreach ($menu->getValues() as $k => $v) {
            if ( !in_array($k, array('menu_id', 'menudetail_id', 'menudetail_FK_menu_id')) ) {
                $newMenu->$k = $v;
            }
        }

        $title = trim(__T('Copy of').' '.$menu->menudetail_title);
        $parent = $parentId ? : $menu->menu_parentId;

        $menus = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu');
        $menus->load('getChildrenMenuInOrder', array('params' => array('menuId' => $parent, 'isBlock' => $menu->menu_type != 'PAGE')));
        $order = $menus->count()+1;

        $newMenu->menu_parentId = $parent;
        $newMenu->menu_order = $order;
        $newMenu->menu_creationDate = new pinax_types_DateTime();

        $newMenu->menudetail_title = $title;
        $newMenu->menudetail_FK_language_id = $languageId;
        $newMenu->menudetail_isVisible = __Config::get('pinaxcms.duplicatePage.visibility');

        $duplicateId = $newMenu->save();

        return $duplicateId;
    }

    /**
     * Duplicate a menu taking count of multilanguage settings
     *
     * @param int $menuId      the menu id to copy
     * @param int $languageId  the main languague id
     * @param int $parentId    the parentId of menu (optional)
     * @return int             new menu id
     */
    public function duplicateMenu($menuId, $languageId, $parentId = null)
    {
        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');

        $duplicateId = $this->duplicateMenuEntry($menuId, $languageId, $parentId);

        foreach ($menu->getLanguagesId() as $lang) {
            if ($lang == $languageId) {
                continue;
            }
            $this->duplicateMenuEntry($menuId, $lang, $parentId, $duplicateId);
        }

        $this->invalidateSitemapCache();

        $evt = array('type' => pinaxcms_contents_events_Menu::MOVE, 'data' => $menuId);
        $this->dispatchEvent($evt);

        return $duplicateId;
    }
    public function showHide($menuId, $languageId, $isShown)
    {
        $menus = $this->getChildMenusFromId($menuId, pinax_ObjectValues::get('org.pinax', 'languageId'), false);
        foreach ($menus as $ar) {
            $this->showHide($ar->menu_id, $languageId, $isShown);
        }

        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.MenuDetail');
        if ($ar->find( array(
                'menudetail_FK_menu_id' => $menuId,
                'menudetail_FK_language_id' => $languageId ))) {

            $ar->menudetail_isVisible = $isShown ? 1 : 0;
            $ar->save();
            $this->invalidateSitemapCache();
        }
// TODO controlare che il menù esiste
    }


    public function lockUnlock($menuId, $state) {
         $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
         if ($ar->load($menuId)) {
            $ar->menu_isLocked = $state ? 1 : 0;
            $ar->save();
            $this->invalidateSitemapCache();
         }
// TODO controlare che il menù esiste
    }


    public function touch($menuId) {
         $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
         if ($ar->load($menuId)) {
            $ar->menu_modificationDate = new pinax_types_DateTime();
            $ar->save();
         }
    }

    public function rename($menuId, $languageId, $title) {
         $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.MenuDetail');
         if ($ar->find( array(
                'menudetail_FK_menu_id' => $menuId,
                'menudetail_FK_language_id' => $languageId ))) {

            $ar->menudetail_title = $title;
            $ar->save();
            $this->invalidateSitemapCache();

            $evt = array('type' => pinaxcms_contents_events_Menu::RENAME, 'data' => $menuId);
            $this->dispatchEvent($evt);
         }
    }

    /**
     * @param int $menuId
     * @param int $languageId
     * @param string $menuUrl
     */
    public function menuUrl($menuId, $languageId, $menuUrl) {
        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.MenuDetail');
        if ($ar->find( array(
               'menudetail_FK_menu_id' => $menuId,
               'menudetail_FK_language_id' => $languageId ))) {

           $ar->menudetail_url = $menuUrl;
           $ar->save();
           $this->invalidateSitemapCache();
        }
   }


    public function invalidateSitemapCache()
    {
        $evt = array('type' => pinaxcms_contents_events_Menu::INVALIDATE_SITEMAP);
        $this->dispatchEvent($evt);
    }
}
