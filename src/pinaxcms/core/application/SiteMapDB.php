<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_core_application_SiteMapDB extends pinax_application_SiteMap
{
    var $_type = 'db';
    private $cache;
    private $application;
    private $speakingUrlManager;

    function __construct()
    {
        parent::__construct();

        $cacheFolder = __Paths::exists('APPLICATION_TO_ADMIN_CACHE') ? __Paths::getRealPath('APPLICATION_TO_ADMIN_CACHE') : __Paths::getRealPath('CACHE');
        $this->cache = pinax_ObjectFactory::createObject('pinax.cache.CacheFunction',
                                                $this,
                                                __Config::get('pinaxcms.sitemap.cacheLife'),
                                                false,
                                                $cacheFolder);

        $this->application = &pinax_ObjectValues::get('org.pinax', 'application');
        $this->speakingUrlManager = $this->application->retrieveProxy('pinaxcms.speakingUrl.Manager');
    }

    function loadTree($forceReload=false)
    {
        if ($forceReload) $this->init();
        $emptyMenu = serialize($this->getEmptyMenu());
        $siteId = pinax_ObjectValues::get('org.pinax', 'siteId');
        $application = $this->application;
        $languageId = $application->getLanguageId();
        $speakingUrl = __Config::get('pinaxcms.speakingUrl') === true;
        $multilanguage = __Config::get('MULTILANGUAGE_ENABLED');

        $this->_siteMapArray = $this->cache->get(__METHOD__.$siteId.'_'.$languageId, array(), function() use ($emptyMenu, $application, $speakingUrl, $multilanguage) {
            $siteMapArray = array();
            $isAdmin = $application->isAdmin();
            $user = $application->getCurrentUser();
            $showDraft = false;
            $showDraft = isset($_GET['draft']) && $_GET['draft'] == '1';
            $languageId = method_exists($application, 'getEditingLanguageId') ? $application->getEditingLanguageId() : $application->getLanguageId();

            // TODO
            // carica le versioni dei vari men�
            // per poter disabilitare i men� che hanno solo la versione bozza
            // $menuVersion = array();
            // $it = &pinax_ObjectFactory::createModelIterator('pinax.models.ContentVersion', 'all', array('filters' => array( 'contentversion_status' =>  'DRAFT', 'contentversion_FK_language_id' => $languageId ) ) );

            // while ( $it->hasMore() )
            // {
            //     $arC = &$it->current();
            //     $it->next();
            //     $menuVersion[ $arC->contentversion_FK_menu_id ] = false;
            // }
            // $it = &pinax_ObjectFactory::createModelIterator('pinax.models.ContentVersion', 'all', array('filters' => array( 'contentversion_status' =>  'PUBLISHED', 'contentversion_FK_language_id' => $languageId ) ) );
            // while ( $it->hasMore() )
            // {
            //     $arC = &$it->current();
            //     $it->next();
            //     $menuVersion[ $arC->contentversion_FK_menu_id ] = true;
            // }
            //
            // $isAdmin = pinax_ObjectValues::get('org.pinax', 'admin', false);
            // $hidePrivatePage = __Config::get( 'HIDE_PRIVATE_PAGE', true );

            // TODO chiamare il proxy
            $menus = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu');
            $menus->load('getAllMenu', array('params' => array( 'languageId' => $languageId)));

            foreach ($menus as $ar) {
                if (is_null($ar->menu_parentId)) continue;
                $menu                     = unserialize($emptyMenu);
                $menu['id']             = $ar->menu_id;
                $menu['parentId']       = $ar->menu_parentId;
                $menu['pageType']       = $ar->menu_pageType;
                $menu['isVisible']      = $ar->menudetail_isVisible == 1 ? true : false;
                $menu['title']          = $ar->menudetail_title; //str_replace( "\n", " ", $ar->title );
                $menu['titleLink']      = $ar->menudetail_titleLink;
                $menu['linkDescription']= $ar->menudetail_linkDescription;
                $menu['depth']          = 1;
                $menu['childNodes']     = array();
                $menu['isLocked']       = $ar->menu_isLocked == '1';
                $menu['hideInNavigation'] = $ar->fieldExists('menudetail_hideInNavigation') && $ar->menudetail_hideInNavigation == '1';
                $menu['url']            = $ar->menudetail_url && $ar->menudetail_url!='alias:' ? $ar->menudetail_url : '';
                $menu['cssClass']       = $ar->menu_cssClass;
                $menu['description']    = $ar->menudetail_description;
                $menu['keywords']       = $ar->menudetail_keywords;
                $menu['seoTitle']       = $ar->menudetail_seoTitle;

                // solo admin
                $menu['order']          = $ar->menu_order;
                $menu['type']           = $ar->menu_type;
                $menu['creationDate']   = pinax_localeDate2default($ar->menu_creationDate);
                $menu['modificationDate']= pinax_localeDate2default($ar->menu_modificationDate);
                $menu['hasComment']     = $ar->menu_hasComment;
                $menu['printPdf']       = $ar->menu_printPdf;

                //$menu['extendsPermissions']    = $ar->menu_extendsPermissions;
                $menu['nodeObj']        = NULL;

                if ($speakingUrl && !$menu['url'] && $ar->speakingurl_value) {
                    $menu['url'] = ($multilanguage ? $ar->language_code.'/' : '').$ar->speakingurl_value;
                }

                $siteMapArray[$menu["id"]] = $menu;
            }
            return $siteMapArray;
        });
        $this->_makeChilds();
    }

    public function invalidate()
    {
        $application = &pinax_ObjectValues::get('org.pinax', 'application');
        $siteId = pinax_ObjectValues::get('org.pinax', 'siteId');
        $iterator = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language', 'all');
        foreach($iterator as $ar) {
            $this->cache->remove('pinaxcms_core_application_SiteMapDB::loadTree'.$siteId.'_'.$ar->language_id, array());
        }
    }

    function &getNodeById($id)
    {
        $id = is_numeric($id) ? $id : strtolower($id);
        if (!array_key_exists($id, $this->_siteMapArray)) {
            $a = NULL;
            return $a;
        }

        if (!is_object($this->_siteMapArray[$id]['nodeObj']))
        {
            $menu = $this->_siteMapArray[$id];
            if (strpos($menu['url'], 'alias:')===0) {
                $url = substr($menu['url'], 6);
                $menu['url'] = $this->speakingUrlManager->makeUrl($url);
            } elseif ($menu['pageType'] == 'Empty') {
                $firstChildURL = $this->getFirstVisibleChildURL($menu);
                if (!is_null($firstChildURL)) {
                    $menu['url'] = $firstChildURL;
                }
            }
            $a =  new pinax_application_SiteMapNode($this, $menu);
            $this->_siteMapArray[$id]['nodeObj'] = &$a;
            return $a;
        }
        return $this->_siteMapArray[$id]['nodeObj'];
    }

    public function getFirstVisibleChildURL($node)
    {
        if (count($node['childNodes'])==0) {
            return null;
        }

        foreach ($node['childNodes'] as $child) {
            $childNode = $this->_siteMapArray[$child];
            if (!$childNode['isVisible']) {
                continue;
            }

            if ($childNode['pageType'] == 'Empty') {
                return $this->getFirstVisibleChildURL($childNode);
            } else {
                return $childNode['url'] ?: __Link::makeUrl('link', ['pageId' => $childNode['id'], 'title' => $childNode['title']]);
            }
        }


    }
}
