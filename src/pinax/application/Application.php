<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_application_Application extends PinaxObject
{
    /**
     * @var string
     */
    var $_pathCore                = '';
    var $_pathApplication        = '';
    var $_pageId                = 0;
    var $_pageType                = '';

    /**
     * @var string
     */
    var $outputMode                = 'html';
    /** @var pinax_application_SiteMapSimple $siteMap */
    var $siteMap                = NULL;
    /** @var pinax_application_SiteMapNode $siteMapMenu */
    var $siteMapMenu            = NULL;
    /** @var pinax_components_ComponentContainer $_rootComponent */
    var $_rootComponent            = NULL;
    /** @var pinax_application_User $_user */
    var $_user                    = NULL;
    var $_language                = '';
    var $_languageId            = 0;

    /**
     * @var bool
     */
    var $_ajaxMode                = false;

    /**
     * @var null|pinax_log_LogBase
     */
    var $_logObj                =     NULL;

    /**
     * @var string
     */
    var $_configHost;
    private $localeLoaded = [];

    /**
     * @var string
     */
    protected $contentType         = 'text/html';

    /** @var pinax_dependencyInjection_Container $container */
    protected $container;

    /** @var pinax_Config $config */
    protected $config;


    /** @var pinax_session $session */
    protected $session;

    /**
     * @param string|array $pathApplication|
     * @param string $pathCore
     * @param string $configHost
     */
    function __construct($pathApplication='', $pathCore='', $configHost='', $container=null)
    {
        if (empty($pathApplication))
        {
            new pinax_Exception(array('[%s] %s', $this->getClassName(), PNX_ERR_EMPTY_APP_PATH));
        }

        $this->initPinaxAccessor();

        $this->container = $container ? : new pinax_dependencyInjection_Container();

        pinax_ObjectValues::setByReference('org.pinax', 'application', $this);
        $this->_pathApplication = $pathApplication;
        $this->_pathCore         = $pathCore;
        $this->_configHost         = $configHost;
        $this->addEventListener(PNX_EVT_USERLOGIN, $this);
        $this->addEventListener(PNX_EVT_USERLOGOUT, $this);

        $this->container = $container ? : new pinax_dependencyInjection_Container();
        $this->addLegacyClassAlias();
        $this->createDefaultServices();
        $this->setExceptionParams();
        $this->importApplicationDependencies();
        $this->_init();
    }


    /**
     * @return void
     */
    function run()
    {
        $this->log( "Run application", PNX_LOG_SYSTEM );
        $this->runStartup();
        $this->_initSiteMap();
        $this->_initRequest();

        pinax_ObjectValues::set('pinax.og', 'url', PNX_HOST.'/go/'.__Request::get('__url__') );
        pinax_require_once_dir(pinax_Paths::getRealPath('APPLICATION_CLASSES'));

        $this->_startProcess();

        if (file_exists(pinax_Paths::get('APPLICATION_SHUTDOWN')))
        {
            // if the shutdown folder is defined all files are included
            pinax_require_once_dir(pinax_Paths::get('APPLICATION_SHUTDOWN'));
        }
    }

    /**
     * @return void
     */
    function runAjax()
    {
        $this->log( "Run ajax application", PNX_LOG_SYSTEM );
        pinax_Request::$translateInfo = false;
        pinax_Request::$skipDecode = $this->config->get( 'AJAX_SKIP_DECODE' );

        $this->_ajaxMode = true;
        $this->run();
    }

    /**
     * @return void
     */
    function runSoft()
    {
        $this->log( "Run application (soft mode)", PNX_LOG_SYSTEM );

        $this->runStartup();
        $this->_initSiteMap();
        $this->_initRequest();

        if (file_exists(pinax_Paths::get('APPLICATION_SHUTDOWN')))
        {
            // if the shutdown folder is defined all files are included
            pinax_require_once_dir(pinax_Paths::get('APPLICATION_SHUTDOWN'));
        }
    }

    /**
     * @return void
     */
    function runStartup()
    {
        if (file_exists(pinax_Paths::get('APPLICATION_STARTUP')))
        {
            // if the startup folder is defined all files are included
            pinax_require_once_dir(pinax_Paths::get('APPLICATION_STARTUP'));
        }

        pinax_defineBaseHost();
        $this->login();
    }


    /**
     * @return void
     */
    function stop()
    {
        pinax_Paths::destroy();
        $this->config->destroy();
        pinax_Request::destroy();
        pinax_Routing::destroy();
        pinax_ObjectValues::removeAll();
    }

    /**
     * @return void
     */
    function _init()
    {
        $this->log( "Start application", PNX_LOG_SYSTEM );

        pinax_Routing::init();
        $this->_initLanguage();
    }


    /**
     * @return void
     */
    function _initRequest()
    {
        pinax_Routing::compileAndParseUrl();
        pinax_Request::init();
    }


    /**
     * @return void
     */
    function _initLanguage()
    {
        $this->log( "initLanguage", PNX_LOG_SYSTEM );
        $currentLanguage = $this->session->get('pinax.language', $this->config->get('DEFAULT_LANGUAGE'));

        $language = pinax_Routing::getLanguage();

        if ($language && $language!=$currentLanguage) {
            // cambio lingua controlla se la lingua richiesta è tra quelle accettate
            $availableLanguages = explode(',', $this->config->get('pinax.languages.available'));
            if (in_array($language, $availableLanguages)) {
               $currentLanguage = $language;
            }
        }

        $this->_language = $currentLanguage;
        // NOTA non viene supportato l'id numerico della lingua
        $this->_languageId = $this->session->get('pinax.languageId',  $this->config->get('DEFAULT_LANGUAGE_ID'));
        pinax_ObjectValues::set('org.pinax', 'language', $this->_language);
        pinax_ObjectValues::set('org.pinax', 'languageId', $this->_languageId);
        $this->session->set('pinax.language', $this->_language);
        $this->session->set('pinax.languageId', $this->_languageId);
        $this->_loadLocale();
    }

    /**
     * @param bool $forceReload
     *
     * @return void
     */
    function createSiteMap($forceReload=false)
    {
        $this->log( "initSiteMap", PNX_LOG_SYSTEM );
        $this->siteMap = &pinax_ObjectFactory::createObject('pinax.application.SiteMapSimple');
        $this->siteMap->getSiteArray($forceReload);
    }

    /**
     * @return void
     */
    function _readPageId()
    {
        $this->log( "readPageId", PNX_LOG_SYSTEM );
        // legge il pageId della pagina da visualizzare
        $this->_pageId = pinax_Request::get('pageId', NULL);
        $url = pinax_Request::get('__url__', NULL);

        if ((!$this->_pageId && __Request::exists('__routingPattern__')) || (!$this->_pageId && !$url))
        {
            $controller = __Request::get( 'controller', '', PNX_REQUEST_ROUTING );
            if ($controller) {
                $controllerClass = $this->container->get($controller, $this);
                pinax_helpers_PhpScript::callMethodWithParams( $controllerClass, 'execute', __Request::getAllAsArray(), true, $this->container);
            }

            $this->_pageId =  $this->config->get('REMEMBER_PAGEID') ? $this->session->get('pinax.pageId', $this->config->get('START_PAGE')) : $this->config->get('START_PAGE');
        }

        // TODO: rimuovere questa specializzazione per il cms
        if (!is_numeric($this->_pageId) && ( $this->getClassName()=='pinaxcms_core_application_application') )
        {
            $this->siteMapMenu    = &$this->siteMap->getMenuByPageType($this->_pageId);
            $this->_pageId        = $this->siteMapMenu->id;
        }
        else
        {
            $this->siteMapMenu    = &$this->siteMap->getNodeById($this->_pageId);
        }

        if ($this->siteMapMenu->hideByAcl) {
            pinax_helpers_Navigation::accessDenied($this->getCurrentUser()->isLogged());
        }

        if (!is_object($this->siteMapMenu) || !$this->siteMapMenu->isVisible)
        {
            $evt = ['type' => PNX_EVT_DUMP_404];
            $this->dispatchEvent($evt);

            if ($this->siteMapMenu && !$this->getCurrentUser()->acl($this->siteMapMenu->id, "visible", true)) {
               pinax_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => $this->config->get('START_PAGE'))));
            }
            $error404Page = $this->config->get( 'ERROR_404');
            if ( !empty( $error404Page ) )
            {
                pinax_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => $error404Page ) ) );
            }
            pinax_helpers_Navigation::notFound();
        }

        if (!empty($this->siteMapMenu->select)) {
            if ($this->siteMapMenu->select=='*') {
                $menu = $this->siteMapMenu->firstChild(true);
            } else {
                $menu = $this->siteMap->getNodeById($this->siteMapMenu->select);
            }
            pinax_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => $menu->id ) ) );
        }

        if ($this->config->get('REMEMBER_PAGEID'))
        {
            $this->session->set('pinax.pageId', $this->_pageId);
        }

    }

    /**
     * @param bool $readPageId
     *
     * @return void
     */
    function _startProcess($readPageId=true)
    {
        $middlewareObj = null;

        $this->log( "startProcess", PNX_LOG_SYSTEM );
        if ( $this->_logObj )
        {
            $this->log( array( 'Request' => __Request::getAllAsArray() ), PNX_LOG_SYSTEM );
        }

        if ($readPageId) {
            $evt = array('type' => PNX_EVT_BEFORE_CREATE_PAGE);
            $this->dispatchEvent($evt);
            $this->_readPageId();
        }

        pinax_ObjectValues::set('pinax.application', 'pageId', $this->_pageId);
        $this->_pageType = $this->siteMapMenu->pageType;

        if (__Request::exists('__middleware__')) {
            $middlewareObj = pinax_ObjectFactory::createObject(__Request::get('__middleware__'));
            // verify the cache before page rendering
            // this type of cache is available only for Static Page
            if ($middlewareObj) {
                $middlewareObj->beforeProcess($this->_pageId, $this->_pageType);
            }
        }

        pinax_ObjectFactory::createPage( $this, $this->_pageType, null, array( 'pathTemplate' => pinax_Paths::get('APPLICATION_TEMPLATE') ) );

        if (!is_null($this->_rootComponent))
        {
            if (!$this->_ajaxMode)
            {
                // serve per resettare lo stato del sessionEx ad ogni caricamento delle pagine
                // altrimenti gli stati vecchi non vengono cancellati
                // quando c'è un cambio di pagina e SessionEx non è usato
                pinax_ObjectFactory::createObject('pinax.SessionEx', '');

                $this->_rootComponent->resetDoLater();
                $this->_rootComponent->init();
                $this->_rootComponent->execDoLater();

                $this->log( "Process components", PNX_LOG_SYSTEM );
                $this->_rootComponent->resetDoLater();

                $evt = array('type' => PNX_EVT_START_PROCESS);
                $this->dispatchEvent($evt);

                if (method_exists($this, 'process_onStart')) $this->process_onStart();
                $this->_rootComponent->process();
                if (method_exists($this, 'process_onEnd')) $this->process_onEnd();
                $this->_rootComponent->execDoLater();

                $evt = array('type' => PNX_EVT_END_PROCESS);
                $this->dispatchEvent($evt);


                // check if enable the PDF output
                if ( $this->getCurrentMenu()->printPdf )
                {
                    $pdfPage = pinax_Paths::getRealPath('APPLICATION_TEMPLATE', 'pdf.php' );
                    if ( $pdfPage !== false )
                    {
                        if ( __Request::get( 'printPdf', '0' ) )
                        {
                            pinax_ObjectValues::set( 'pinax.application', 'pdfMode', __Request::get( 'printPdf', '0' ) == '1' );
                        }
                    }
                    else
                    {
                        $this->getCurrentMenu()->printPdf = false;
                    }
                }

                $evt = array('type' => PNX_EVT_START_RENDER);
                $this->dispatchEvent($evt);
                $this->_rootComponent->resetDoLater();
                if (method_exists($this, 'render_onStart')) $this->render_onStart();

                $this->addJScoreLibraries();

                $output = $this->_rootComponent->render();

                if (method_exists($this, 'render_onEnd')) $this->render_onEnd();
                $this->_rootComponent->execDoLater();

                $evt = array('type' => PNX_EVT_END_RENDER);
                $this->dispatchEvent($evt);

                $headerErrorCode = __Request::get( 'pinaxHeaderCode', '' );
                if ( $headerErrorCode )
                {
					$message = $headerErrorCode.' '.pinax_helpers_HttpStatus::getStatusCodeMessage( (int)$headerErrorCode );
					header( "HTTP/1.1 ".$message );
					header( "Status: ".$message );
                }
                header("Content-Type: ".$this->contentType."; charset=".$this->config->get('CHARSET'));

                if ($middlewareObj) {
                    // verify the cache after content rendering
                    $middlewareObj->afterRender($output);
                }

                echo $output;
            }
            else
            {
                $this->startProcessAjax();
            }
        }
        else
        {
            // TODO
            // visualizzare errore
        }
    }

    /**
     * @return bool
     */
    protected function startProcessAjax()
    {
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header('Expires: -1');

        $this->_rootComponent->resetDoLater();
        $this->_rootComponent->init();
        $this->_rootComponent->execDoLater();

        $evt = array('type' => PNX_EVT_START_PROCESS);
        $this->dispatchEvent($evt);

        $acl = $this->_rootComponent->getAttribute( 'acl' );
        if ($acl) {
            list( $service, $action ) = explode( ',', $acl );
            if (!$this->_user->acl($service, $action, false)) {
                pinax_helpers_Navigation::accessDenied(false);
            }
        }

        $ajaxTarget = pinax_Request::get('ajaxTarget');
        $targetComponent = &$this->_rootComponent->getComponentById($ajaxTarget);
        if (is_null($targetComponent)) {
            // prima prova a creare i figli in modo ritardato
            // questo è usato nella gestione degli stati
            $this->_rootComponent->deferredChildCreation(true);
            $targetComponent = &$this->_rootComponent->getComponentById($ajaxTarget);


            // se il targetComponent è ancora nullo
            // prova a lanciare il process di tutti i figli
            if (is_null($targetComponent)) {
                $this->_rootComponent->process();
                $targetComponent = &$this->_rootComponent->getComponentById($ajaxTarget);
                if (is_null($targetComponent)) {
                    return false;
                }
            }
        }

        $ajaxMethod = __Request::get('ajaxMethod', 'process_ajax');
        if (method_exists($targetComponent, $ajaxMethod)) {
            pinax_Request::remove('pageId');
            pinax_Request::remove('ajaxTarget');
            $result = $targetComponent->{$ajaxMethod}();
        } else {
            $result = $this->processAjaxCallController($targetComponent);
        }

        if (!$targetComponent->controllerDirectOutput() && !is_array($result) && !is_object($result)) $result = array('status'=> ($result===true ? 'success' : 'error'));
        if ( is_array($result) && isset( $result['html'] ) ) {
            header("Content-Type: ".$this->contentType."; charset=".$this->config->get('CHARSET'));
            echo $result['html'];
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode( $result );
        }
        return true;
    }

    /**
     * @param pinax_components_Component $targetComponent
     *
     * @return array
     */
    private function processAjaxCallController($targetComponent)
    {
        if ( __Request::exists('controllerName')) {
            $targetComponent->setAttribute('controllerName', __Request::get('controllerName'));
        }
        $result = array( 'status' => false );
        $r = $targetComponent->callController();
        if ($r !== null && $r !== false)
        {
            if ( $targetComponent->controllerDirectOutput() ) return $r;

            $result['status'] = true;
            if ( is_array( $r ) && isset( $r[ 'error' ] ) )
            {
                $result['status'] = false;
                $result['error'] = $r[ 'error' ];
                return $result;
            }

            $outputFormatInHtml = false;
            $html = '';

            if ( is_array( $r ) && isset( $r[ 'sendOutput' ] ) )
            {
                // controlla se c'è renderizzare dei componenti da mandare all'output
                __Request::set('action', isset($r['sendOutputState']) ? $r['sendOutputState'] : '');
                $outputFormatInHtml = isset( $r[ 'sendOutputFormat' ] ) && $r[ 'sendOutputFormat' ] == 'html';
                $outputComponent = isset($r['sendOutputComponent']) ? $r['sendOutputComponent'] : '';
                $this->_rootComponent->process();

                $componentsId = $r[ 'sendOutput' ];
                unset( $r[ 'sendOutput' ] );
                unset( $r[ 'sendOutputState' ] );
                if ( !is_array( $componentsId ) ) {
                    $componentsId = array( $componentsId );
                }

                foreach( $componentsId as $id ) {
                    $c = $this->_rootComponent->getComponentById( $id );
                    if ( is_object( $c ) ) {
                        if ($c->state==COMPONENT_STATE_INIT) {
                            $c->process();
                        }

                        $outputComponentInstance = $outputComponent ? $this->_rootComponent->getComponentById( $outputComponent ) : $this->_rootComponent;

                        $outputComponentInstance->_output = array();
                        $c->render();

                        $r[ $id ] = '';
                        foreach( $outputComponentInstance->_output as $o ) {
                            if ( strpos($o['editableRegion'], '__') !== false ) continue;
                            $r[ $id ] .= $o[ 'code' ];
                            $html .= $o[ 'code' ];
                        }
                    }
                }
            }

            if ( $outputFormatInHtml ) {
                $result['html'] = $html;
            } else {
                $result['result'] = $r;
            }
        }
        return $result;
    }

    /**
     * @param pinax_components_Component $component
     *
     * @return void
     */
    function addChild(&$component)
    {
        $this->_rootComponent = &$component;
    }

    /**
     * @return pinax_components_Component
     */
    function &getRootComponent()
    {
        return $this->_rootComponent;
    }

    /**
     * @return string
     */
    function getOutputMode()
    {
        return $this->outputMode;
    }

    /**
     * @return string
     */
    function getPageId()
    {
        return strtolower( $this->_pageId );
    }

    /**
     * @param string $id
     *
     * @return void
     */
    function setPageId($id)
    {
        $this->_pageId = $id;
        $this->siteMapMenu = &$this->siteMap->getNodeById($this->_pageId);
    }

    /**
     * @return string
     */
    function getPageType()
    {
        return $this->_pageType;
    }

    /**
     * @return pinax_application_SiteMapNode
     */
    function &getCurrentMenu()
    {
        return $this->siteMapMenu;
    }

    /**
     * @return pinax_application_User
     */
    function &getCurrentUser()
    {
        $user = &pinax_ObjectValues::get('org.pinax', 'user');
        return $user;
    }

    /**
     * @return pinax_application_SiteMapSimple
     */
    function &getSiteMap()
    {
        return $this->siteMap;
    }

    /**
     * @return int
     */
    function getLanguageId()
    {
        return $this->_languageId;
    }

    /**
     * @param int $value
     *
     * @return void
     */
    function setLanguageId($value)
    {
        $this->_languageId = $value;
        pinax_ObjectValues::set('org.pinax', 'languageId', $this->_languageId);
    }

    /**
     * @return string
     */
    function getLanguage()
    {
        return strtolower($this->_language);
    }

    /**
     * @param $value
     *
     * @return void
     */
    function setLanguage($value)
    {
        $value = strtolower($value);
        if ($this->_language != $value)
        {
            $this->_language = $value;
            $this->_loadLocale();
        }
    }

    /**
     * @return void
     */
    function _loadLocale()
    {
        // importa i file di localizzazione
        if (file_exists(pinax_Paths::getRealPath('CORE_CLASSES').'pinax/locale/'.$this->getLanguage().'.php'))
        {
            require(pinax_Paths::getRealPath('CORE_CLASSES').'pinax/locale/'.$this->getLanguage().'.php');
        }
        else
        {
            require(pinax_Paths::getRealPath('CORE_CLASSES').'pinax/locale/en.php');
        }
    }

    /**
     * @return void
     */
    function addJScoreLibraries()
    {
        if (!pinax_ObjectValues::get('pinax.JS.Core', 'add', false) && $this->config->get( 'PINAX_ADD_CORE_JS' ) )
        {
            pinax_ObjectValues::set('pinax.JS.Core', 'add', true);
            $this->addJSLibCore();
            if ($this->config->get('DEBUG')) {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('dejavu/strict/dejavu.js?v='.PNX_CORE_VERSION), 'head');
            } else {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('dejavu/loose/dejavu.min.js?v='.PNX_CORE_VERSION), 'head');
            }
            $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('Pinax.js?v='.PNX_CORE_VERSION), 'head');
            $filename = $this->getLanguage().'.js';
            if(!file_exists(__DIR__."/../../../../static/js/locale/".$filename)) {
                $filename = 'en.js';
            }
            $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile($filename, 'locale/'), 'head');
        }
    }

    /**
     * @return void
     */
    function addJSLibCore()
    {
        if (!pinax_ObjectValues::get('pinax.JS.lib', 'add', false) && $this->config->get( 'PINAX_ADD_JS_LIB' ) )
        {
            pinax_ObjectValues::set('pinax.JS.lib', 'add', true);
            if ( $this->config->get( 'PINAX_ADD_JQUERY_JS' ) )
            {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkStaticJSfile( 'jquery/' . $this->config->get('PINAX_JQUERY' ) ), 'head');
                if ( $this->config->get( 'PINAX_ADD_JQUERYUI_JS' ) )
                {
                    $this->_rootComponent->addOutputCode( pinax_helpers_JS::linkStaticJSfile( 'jquery/jquery-ui/' . $this->config->get('PINAX_JQUERYUI' ) ), 'head');
                    $this->_rootComponent->addOutputCode( pinax_helpers_CSS::linkStaticCSSfile( 'jquery/jquery-ui/' . $this->config->get('PINAX_JQUERYUI_THEME' ) ), 'head');
                }
            }
        }
    }

    /**
     * @return void
     */
    function addLightboxJsCode()
    {
        if (!pinax_ObjectValues::get('pinax.JS.Lightbox', 'add', false) && $this->config->get( 'PINAX_ADD_JS_LIB' ) )
        {
			$colorboxSlideshowAuto = $this->config->get('COLORBOX_SLIDESHOWAUTO');
			$colorboxSlideshowAuto = $colorboxSlideshowAuto ? 'true' : 'false';
			pinax_ObjectValues::set('pinax.JS.Lightbox', 'add', true);
            $this->addJSLibCore();

            $this->_rootComponent->addOutputCode( pinax_helpers_CSS::linkStaticCSSfile('jquery/colorbox/pinax/colorbox.css' ), 'head' );
            $this->_rootComponent->addOutputCode( pinax_helpers_JS::linkStaticJSfile('jquery/colorbox/jquery.colorbox-min.js' ), 'head' );
            $this->_rootComponent->addOutputCode(pinax_helpers_JS::JScode( 'jQuery(document).ready(function() { jQuery("a.js-lightbox-image").colorbox({ photo:true, slideshow:true, slideshowAuto:'.$colorboxSlideshowAuto.', slideshowSpeed: Pinax.slideShowSpeed, current: "{current} di {total}",
        previous: "'.__T('PNX_PREVIOUS').'",
        next: "'.__T('PNX_NEXT').'",
        close: "'.__T('PNX_COLSE').'",
        slideshowStart: "'.__T('PNX_SLIDESHOW_START').'",
        slideshowStop: "'.__T('PNX_SLIDESHOW_STOP').'" })  });' ), 'head');

            $this->_rootComponent->addOutputCode(pinax_helpers_JS::JScode( 'jQuery(document).ready(function() { jQuery("a.js-lightbox-inline").colorbox({inline:true, title: false})});' ), 'head');
        }
    }

    /**
     * @return void
     */
    function addZoomJsCode()
    {
        if (!pinax_ObjectValues::get('pinax.JS.Zoom', 'add', false) && $this->config->get( 'PINAX_ADD_JS_LIB' ) )
        {
            pinax_ObjectValues::set('pinax.JS.Zoom', 'add', true);
            $this->addJSLibCore();
            $this->_rootComponent->addOutputCode( pinax_helpers_JS::linkStaticJSfile('OpenSeadragon/OpenSeadragon.js' ), 'head' );
            $this->_rootComponent->addOutputCode( '<div id="zoomContainer" data-cache="'.__Paths::get('CACHE').'"></div>' );
        }
    }

    /**
     * @param string $formName
     *
     * @return void
     */
    function addValidateJsCode( $formName=null )
    {
        if (!$this->config->get('PINAX_ADD_VALIDATE_JS')) return;
        if (!pinax_ObjectValues::get('pinax.JS.Validate', 'add', false) && $this->config->get( 'PINAX_ADD_JS_LIB' ) )
        {
            // Validate
            pinax_ObjectValues::set('pinax.JS.Validate', 'add', true);
            $this->addJSLibCore();

            if ( file_exists( pinax_Paths::get('STATIC_DIR').'jquery/jquery.validationEngine/jquery.validationEngine-'.$this->getLanguage().'.js' ) )
            {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkStaticJSfile( 'jquery/jquery.validationEngine/jquery.validationEngine-'.$this->getLanguage().'.js' ), 'head');
            }else {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::linkStaticJSfile( 'jquery/jquery.validationEngine/jquery.validationEngine-en.js' ), 'head');
            }
            $this->_rootComponent->addOutputCode( pinax_helpers_JS::linkStaticJSfile( 'jquery/jquery.validationEngine/jquery.validationEngine.js' ), 'head');
            $this->_rootComponent->addOutputCode( pinax_helpers_CSS::linkStaticCSSfile( 'jquery/jquery.validationEngine/validationEngine.jquery.css' ), 'head');
        }

        if ( !is_null( $formName ) && $this->config->get( 'PINAX_ADD_JS_LIB' ) )
        {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::JScode( 'jQuery(document).ready(function() { $("#'.$formName.'").validationEngine( "attach", { validationEventTrigger: "none", scroll: false, showAllErrors: false } ); });' ), 'head');

//                $this->_rootComponent->addOutputCode(pinax_helpers_JS::JScode( '$(document).ready(function() { $("#'.$formName.'").validationEngine(); });' ), 'head');
        }
    }

    /* events listener */
    /**
     * @return void
     */
    function login()
    {
        $this->log( "login", PNX_LOG_SYSTEM );
        if ($this->session->get('pinax.userLogged'))
        {
            $this->log( "user is logged", PNX_LOG_SYSTEM );
            $user = $this->session->get('pinax.user');

            // crea l'utente
            $this->_user = &pinax_ObjectFactory::createObject('pinax.application.User', $user);
            pinax_ObjectValues::setByReference('org.pinax', 'user', $this->_user);
            pinax_ObjectValues::set('org.pinax', 'userId', $this->_user->id);

            $this->container->set('org.pinax.user', $this->_user);

            if ($this->config->get('USER_LOG'))
            {
                $this->log( "log user access", PNX_LOG_SYSTEM );
                $arLog = &pinax_ObjectFactory::createModel('pinax.models.UserLog');
                $arLog->load($user['logId']);
                $arLog->userlog_FK_user_id = $user['id'];
                $arLog->save();
            }
        }
        else
        {
            $this->createDummyUser();
        }
        }

    /**
     * @return void
     */
    public function onLogout()
    {
        // create dummy user
        $this->createDummyUser();
    }

    /**
     * @return bool
     */
    function isAdmin()
    {
        return false;
    }

    /**
     * @return bool
     */
    function canViewPage()
    {
        return true;
    }

    /**
    * @return bool
    */
    public function isAjaxMode()
    {
        return $this->_ajaxMode;
    }

    /**
     * @return void
     */
    private function createDummyUser()
    {
        // create dummy user
        $user = 0;
        $this->_user = &pinax_ObjectFactory::createObject('pinax.application.User', $user);
        pinax_ObjectValues::setByReference('org.pinax', 'user', $this->_user);
        pinax_ObjectValues::set('org.pinax', 'userId', 0);
    }

    /**
     * @return pinax_dependencyInjection_Container
     */
    public function getContainer()
    {
        return $this->container;
    }

     /**
     * Create defailt services in the container
     *
     * @return void
     */
    protected function createDefaultServices()
    {
         // inizializzazione delle classi
        // classe statica per la gestione dei path
        pinax_Paths::init($this->_pathApplication, $this->_pathCore);

        $this->container->set('pinax.application', $this);
        $this->container->set('pinax_interfaces_Logger', $this);

        $this->createConfig();
        $this->createSession();
    }

    /**
     * Create Config class
     *
     * @return void
     */
    protected function createConfig()
    {
        $this->config = new pinax_Config($this->_configHost);
        $this->container->setAndCreateFacade(['pinax.config', 'pinax_interfaces_Config'], $this->config, '__Config');
    }

    /**
     * Create Session class
     *
     * @return void
     */
    protected function createSession()
    {
        $sessionPrefix = $this->config->get('SESSION_PREFIX');
        if (empty($sessionPrefix)) {
            // se non è stato specificato un prefisso per la sessione
            // viene usato il nome dell'applicazione
            $sessionPrefix = str_replace(array('.', ' ', '/'), '', $this->_pathApplication).'_';
            $this->config->set('SESSION_PREFIX', $sessionPrefix);
        }

        // inizializzazione della sessione
        $this->session = new pinax_Session(
                $sessionPrefix,
                $this->config->get('SESSION_TIMEOUT'),
                $this->config->get('pinax.session.store'),
                $this->config->get('pinax.session.store.prefix')
            );

        $this->container->setAndCreateFacade(['pinax.session', 'pinax_interfaces_Session'], $this->session, '__Session');
    }


    /**
     * Import the application dependencies from config/di.php
     *
     * @return void
     */
    protected function importApplicationDependencies()
    {
        $diConfigFile = __Paths::get('APPLICATION_CONFIG').'di.php';
        if (file_exists($diConfigFile)) {
            $this->container->readConfigiration($diConfigFile);
        }
    }

    /**
     * Set Exception application name and debug mode
     *
     * @return void
     */
    protected function setExceptionParams()
    {
        pinax_Exception::$applicationName = $this->config->get('APP_NAME');
        pinax_Exception::$debugMode = $this->config->get('DEBUG')==true;
    }

    /**
     * Add legacy class alias
     *
     * @return void
     */
    protected function addLegacyClassAlias()
    {
        $classAlias = ['__Request', '__Routing', '__Registry', '__Assets', '__Modules', '__ObjectFactory', '__ObjectValues', '__Html', '__Link', '__String', '__Paths'];
        foreach($classAlias as $class) {
            include_once(sprintf('%s/../dependencyInjection/facade/%s.php', __DIR__, $class));
        }
    }

    /**
     * @return void
     */
    protected function initPinaxAccessor()
    {
        PinaxClassLoader::register();

        if (!defined('PNX_TESTS')) {
           PinaxErrorHandler::register();
        }
    }


    protected $sitemapFactory = null;

    /**
     * @return void
     */
    public function sitemapFactory($factory)
    {
        $this->sitemapFactory = $factory;
    }

    /**
     * @return void
     */
    function _initSiteMap($forceReload=false)
    {
        if (method_exists($this->sitemapFactory, '__invoke')) {
            $this->siteMap = $this->sitemapFactory->__invoke($forceReload);
        } else {
            $this->createSiteMap($forceReload);
        }
    }

    /**
     * @return void
     */
    public function loadModuleLocale($classPath)
    {
        $this->addLoadLocale($classPath);
        $classPath = rtrim(str_replace(['.', '\\'], '/', $classPath), '*');
        $language = $this->getLanguage();
        $searchPath = pinax_Paths::getClassSearchPath();
        foreach($searchPath as $p) {
            $fileToCheck = pinax_resolvePsr4Path($p, $classPath);
            if (pinax_loadLocaleReal($fileToCheck, $language)) {
                break;
            }
        }
    }

    /**
     * @return void
     */
    public function addLoadLocale($classPath)
    {
        if (!in_array($classPath, $this->localeLoaded)) {
            $this->localeLoaded[] = $classPath;
        }
    }

    /**
     * @return void
     */
    protected function reloadLocale()
    {
        $this->_loadLocale();
        foreach($this->localeLoaded as $classPath) {
            $this->loadModuleLocale($classPath);
        }
    }

    /**
     * @param string $command
     *
     * @return mixed
     */
    public function executeCommand( $command )
	{
		$controller = $this->getContainer()->get($command, $this);
		if (!is_object($controller)) {
			throw new \Exception(sprintf('%s: can\'t create class %s', __METHOD__, $command));
		}

		$params = func_get_args();
		array_shift($params);

		return pinax_helpers_PhpScript::callMethodWithParams( $controller,
																	'execute',
																	$params,
																	true,
																	$this->getContainer());

	}
}
