<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_core_application_Application extends pinax_mvc_core_Application
{
    var $_aclPage;
    var $_siteProperty;
    var $_templateName;

    function __construct($pathApplication='', $pathCore='', $configHost='')
    {
        pinax_Paths::init($pathApplication, $pathCore);
        pinax_Paths::add('APPLICATION_TO_ADMIN', pinax_Paths::get('APPLICATION'));
        pinax_Paths::add('APPLICATION_TO_ADMIN_CACHE', pinax_Paths::get('APPLICATION').'../cache/');
        pinax_Paths::addClassSearchPath(realpath(__DIR__.'/../../../../'));
        parent::__construct($pathApplication, $pathCore, $configHost);
    }

    public function runSoft()
    {
        parent::runSoft();
        $this->readSiteProperties();
    }

    function _init()
    {
        pinax_ObjectValues::set('org.pinax', 'siteId', $this->config->get('pinax.multisite.id'));
        parent::_init();
    }

    function _initLanguage()
    {
        $this->log( "initLanguage", PNX_LOG_SYSTEM );

        $this->_language = $this->session->get('pinax.language', NULL);
        $this->_languageId = $this->session->get('pinax.languageId', NULL);

        if (is_null($this->_languageId))
        {
            $lang = $this->config->get('MULTILANGUAGE_ENABLED') ? pinax_Request::get('language', pinax_Routing::getLanguage()) : substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

            // try to read the browser language
            $this->log( "Read browser language", PNX_LOG_SYSTEM );
            $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
            if (!$ar->find(array('language_code' => $lang, 'language_isVisible' => '1'))) {
                $this->log( "Read defaul language", PNX_LOG_SYSTEM );
                $ar->emptyRecord();
                $ar->find(array('language_isDefault' => 1));
            }

            $this->_language = $ar->language_code;
            $this->_languageId = $ar->language_id;
            $this->session->set('pinax.language', $this->_language);
            $this->session->set('pinax.languageId', $this->_languageId);
        }

        pinax_ObjectValues::set('org.pinax', 'languageId', $this->_languageId);

        // importa i file di localizzazione
        if (file_exists(pinax_Paths::getRealPath('CORE_CLASSES').'pinax/locale/'.$this->getLanguage().'.php'))
        {
            $this->log( "Import locale file", PNX_LOG_SYSTEM );
            require_once(pinax_Paths::getRealPath('CORE_CLASSES').'pinax/locale/'.$this->getLanguage().'.php');
        } else {
            require_once(pinax_Paths::getRealPath('CORE_CLASSES').'pinax/locale/en.php');
        }
    }

    function createSiteMap($forceReload=false)
    {
        $this->log( "initSiteMap", PNX_LOG_SYSTEM );
        $this->siteMap = &pinax_ObjectFactory::createObject('pinaxcms.core.application.SiteMapDB');
        $this->siteMap->getSiteArray($forceReload);

        // controlla se l'utente ha i permessi per modificare la pagina
        // per velocizzare vengono precaricate tutte le relazioni in memoria
        $this->_aclPage = array();
        if ( $this->config->get( 'ACL_ENABLED' ) )
        {

            $this->_aclPage = $this->session->get('pinax.aclFront', NULL);
            if (is_null($this->_aclPage)) {
                $this->_aclPage = array();
                $it = pinax_ObjectFactory::createModelIterator( 'pinax.models.Join', 'all', array( 'filters' => array( 'join_objectName' => __Config::get('DB_PREFIX') . 'menus_tbl#rel_aclFront' ) ) );
                foreach ($it as $arC) {
                    if ( !isset( $this->_aclPage[ $arC->join_FK_source_id ] ) )
                    {
                        $this->_aclPage[ $arC->join_FK_source_id ] = array();
                    }
                    $this->_aclPage[ $arC->join_FK_source_id ][] = $arC->join_FK_dest_id ;
                }

                // scorre tutti i menù per attribuire l'acl ai menù che non ce l'hanno
                // ereditandola dal padre
                $siteMapIterator = &pinax_ObjectFactory::createObject('pinax.application.SiteMapIterator', $this->siteMap);
                while (!$siteMapIterator->EOF) {
                    $n = $siteMapIterator->getNode();
                    $siteMapIterator->moveNext();

                    if ( !isset($this->_aclPage[$n->id])) {
                        $n2 = $n;
                        while (true) {
                            if ( $n2->parentId == 0 ) break;
                            $parentNode =  $n2->parentNode();
                            $n2 = $parentNode;
                            if ( isset( $this->_aclPage[$parentNode->id])) {
                                $this->_aclPage[$n->id] = $this->_aclPage[$parentNode->id];
                                break;
                            }
                        }
                    }
                }
                $this->session->set('pinax.aclFront', $this->_aclPage);
            }
        }
    }

    function _startProcess($readPageId=true)
    {
        $this->log( "startProcess", PNX_LOG_SYSTEM );
        $this->checkSwitchLanguage();

        $controller = __Request::get( 'controller', '', PNX_REQUEST_ROUTING );
        if ($controller)
        {
            $controllerClass = $this->container->get($controller, null, $this);
            pinax_helpers_PhpScript::callMethodWithParams( $controllerClass, 'execute', __Request::getAllAsArray(), true, $this->container);
        }

        $this->readSiteProperties();
        $this->setTemplateFolder();
        if ($readPageId) {
            $evt = array('type' => PNX_EVT_BEFORE_CREATE_PAGE);
            $this->dispatchEvent($evt);
            $this->_readPageId();
        }

        if ($this->siteMapMenu->isVisible===false)
        {
            while(true)
            {
                $parentMenu = &$this->siteMapMenu->parentNode();

                if (is_null($parentMenu))
                {
                    // ERROR
                    $e = new pinax_Exception(array('[%s] %s', $this->getClassName(), __T(PNX_ERR_EMPTY_APP_PATH)));
                }

                $this->siteMapMenu = &$parentMenu;
                if ($parentMenu->isVisible===true)
                {
                    $this->_pageId = $this->siteMapMenu->id;
                    break;
                }
            }
        }

        if ($this->siteMapMenu->pageType=='Empty') {
            $currentPage = &$this->siteMapMenu;
            $childPos = 0;
            while (true) {
                $childNodes = $currentPage->childNodes();
                if (!count($childNodes)) {
                    pinax_helpers_Navigation::gotoUrl(PNX_HOST);
                    return;
                }

                $tempPage = &$childNodes[$childPos];
                if ($tempPage->type=='BLOCK') {
                    $childPos++;
                    continue;
                }

                $currentPage = &$tempPage;
                $childPos = 0;
                if ($currentPage->pageType!='Empty') {
                    $this->siteMapMenu = &$currentPage;
                    $this->_pageId = $currentPage->id;
                    break;
                }
            }
        }

        parent::_startProcess(false);
    }

    function canViewPage( $page=null )
    {
        if (__Config::get('ACL_ROLES')) {
            if ( is_null( $page ) ) {
                $page = $this->_pageId;
            }

            $user = &$this->getCurrentUser();
            if ( isset( $this->_aclPage[ $page ] ) &&
                    !$user->acl(__Config::get('SITEMAP_ID'), 'all') &&
                    !$user->acl('pinaxcms_ContentsEdit', 'all')) {
                return $user->isInRoles($this->_aclPage[ $page ]);
            }
        }

        return true;
    }

    function getSiteProperty()
    {
        return $this->_siteProperty;
    }

    function getTemplateName()
    {
        return $this->_templateName;
    }

    private function setTemplateFolder()
    {
        $this->_templateName = __Config::get('pinaxcms.template.default');
        if (__Config::get('pinaxcms.contents.templateEnabled')) {
            $this->_templateName = pinax_Registry::get( __Config::get( 'REGISTRY_TEMPLATE_NAME' ), $this->_templateName);
        }

        if (__Config::get('pinaxcms.mobile.template.enabled')) {
            $browser = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") || strpos($_SERVER['HTTP_USER_AGENT'],"Android");
            if ($browser === true) {
                if ( file_exists( pinax_Paths::get('APPLICATION_STATIC').'templates/'.$this->_templateName.'-mobile' ) ) {
                    $this->_templateName .= '-iPhone';
                }
                else if ( file_exists( pinax_Paths::get('APPLICATION_STATIC').'templates/mobile' ) )
                {
                    $this->_templateName = 'iPhone';
                }
            }
        }

        $pathBaseTemplate = pinax_Paths::get('APPLICATION_STATIC').'templates/';
        pinax_Paths::set('APPLICATION_TEMPLATE', $pathBaseTemplate.$this->_templateName.'/');
        pinax_Paths::set('APPLICATION_TEMPLATE_DEFAULT', $pathBaseTemplate.'Default/');
        pinax_Paths::addClassSearchPath($pathBaseTemplate.$this->_templateName.'/classes/');
        pinax_loadLocaleReal( $pathBaseTemplate.$this->_templateName.'/classes', $this->getLanguage() );
    }


    private function readSiteProperties()
    {
        $siteProp = unserialize(pinax_Registry::get($this->config->get( 'REGISTRY_SITE_PROP' ).$this->getLanguage(), ''));
        if (!is_array($siteProp))
        {
            // if the site properties are not defined
            // try to read the properties from default language
            $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
            $ar->language_isDefault = 1;
            $ar->find();

            $this->session->set('pinax.default.language', $ar->language_code);
            $this->session->set('pinax.default.languageId', $ar->language_id);

            $siteProp = pinax_Registry::get($this->config->get( 'REGISTRY_SITE_PROP' ).$ar->language_code, '');
            pinax_Registry::set($this->config->get( 'REGISTRY_SITE_PROP' ).$this->getLanguage(), $siteProp);
            $siteProp = unserialize($siteProp);
        }
        if (!is_array($siteProp))
        {
            $siteProp = array();
        }
        $this->_siteProperty = $siteProp;
    }

    private function checkSwitchLanguage()
    {
        $language = pinax_Request::get('language', pinax_Routing::getLanguage());

        if ((!empty($language) && $language!=$this->_language) || __Request::exists('language', PNX_REQUEST_GET))
        {
            // cambio lingua
            $this->log( "change language", PNX_LOG_SYSTEM );
            $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
            $ar->language_code = $language;
            if (!$ar->find()) {
                $evt = ['type' => PNX_EVT_DUMP_404];
                $this->dispatchEvent($evt);

                $error404Page = $this->config->get( 'ERROR_404');
                if ( !empty( $error404Page ) ) {
                    pinax_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => $error404Page ) ) );
                }
                pinax_helpers_Navigation::notFound();
            }

            $this->reinitLanguage($ar->language_id, $ar->language_code);

            // ricarica la struttura del sito per avere i titoli aggiornati
            $this->_initSiteMap(true);
            $this->reloadLocale();
            pinax_Routing::destroy();
            pinax_Routing::init($this->_language);

            // controlla se il routing ha definito un urlResolver
            $speakingUrlManager = $this->retrieveProxy('pinaxcms.speakingUrl.Manager');
            $urlResolver = $speakingUrlManager->getResolver(__Request::get('cms:urlResolver', 'pinaxcms.core.models.Content'));
            $url = $urlResolver->makeUrlFromRequest();

            pinax_helpers_Navigation::gotoUrl($url);
        }
    }

    function _readPageId()
    {
        if (!$this->isAjaxMode()) {
            $url = rtrim(pinax_Request::get('__url__', NULL), '/');
            if ($this->config->get('MULTILANGUAGE_ENABLED') && (!$url || strlen($url)==2)) {
                $this->checkIfReloadLanguage($url);
                $homeMenu = $this->siteMap->getHomeNode();
                $url = $homeMenu->url ? PNX_HOST.'/'.$homeMenu->url : __Routing::makeUrl('link', ['pageId' => $homeMenu->id]);
                $message = '301 '.pinax_helpers_HttpStatus::getStatusCodeMessage(301);
                header('Content-Length: 0');
                header('HTTP/1.1 '.$message );
                header('Status: '.$message );
                header('Location: '.$url);
                exit;
            }
        }

        parent::_readPageId();
    }

    /**
     * Check if teh language must be reloaded
     * @param  string $language
     */
    private function checkIfReloadLanguage($language)
    {
        if (strlen($language)!=2) return;

        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
        $ar->language_code = $language;
        if (!$ar->find()) {
            return '';
        }

        if ($ar->language_code!=$this->_language) {
            $this->reinitLanguage($ar->language_id, $ar->language_code);
        }
    }

    /**
     * Set the new language and releade the SiteMap
     * @param  int $languageId
     * @param  string $languageCode
     */
    private function reinitLanguage($languageId, $languageCode)
    {
        $this->session->set('pinax.language', $languageCode);
        $this->session->set('pinax.languageId', $languageId);
        pinax_ObjectValues::set('org.pinax', 'languageId', $languageId);
        $this->_languageId = $languageId;
        $this->_language = $languageCode;

        // ricarica la struttura del sito per avere i titoli aggiornati
        $this->_initSiteMap(true);
        $this->reloadLocale();
        pinax_Routing::destroy();
        pinax_Routing::init($this->_language);
    }
}
