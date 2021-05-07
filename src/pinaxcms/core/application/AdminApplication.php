<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_core_application_AdminApplication extends pinax_mvc_core_Application
{
    var $_pathApplicationToAdmin;
    public $hostApplicationToAdmin;

    function __construct($pathApplication='', $pathCore='', $pathApplicationToAdmin='', $configHost='')
    {
        $this->_pathApplicationToAdmin = $pathApplicationToAdmin;

        pinax_ObjectValues::set('org.pinax', 'admin', true);
        pinax_Paths::init($pathApplication, $pathCore);
        pinax_Paths::set('APPLICATION_MEDIA_ARCHIVE', $this->_pathApplicationToAdmin.'mediaArchive/');
        pinax_Paths::add('APPLICATION_TO_ADMIN', $this->_pathApplicationToAdmin);
        pinax_Paths::add('APPLICATION_TO_ADMIN_CACHE', $this->_pathApplicationToAdmin.'../cache/');
        pinax_Paths::add('APPLICATION_TO_ADMIN_PAGETYPE', $this->_pathApplicationToAdmin.'pageTypes/');


        $relStaticPath = str_replace(pinax_Paths::get('APPLICATION'), '', pinax_Paths::get('APPLICATION_TO_ADMIN'));
        pinax_Paths::add('APPLICATION_TO_ADMIN_BASE', $relStaticPath);
        pinax_Paths::add('STATIC_DIR', $relStaticPath.'static/');
        pinax_Paths::add('CORE_STATIC_DIR', $relStaticPath.'static/pinax/core/js');
        pinax_Paths::add('PINAX_CMS_STATIC_DIR', $relStaticPath.'static/pinax/core/js');
        pinax_Paths::add('PINAX_CMS_CLASSES', __DIR__.'/../../../');
        pinax_Paths::addClassSearchPath( __DIR__.'/../../../' );
        pinax_Paths::addClassSearchPath( $this->_pathApplicationToAdmin.'classes/' );

        // pinax_Config::init($configHost);
        // pinax_Config::set('SESSION_PREFIX', pinax_Config::get('SESSION_PREFIX').'_admin');

        parent::__construct($pathApplication, $pathCore, $configHost);
        $this->addEventListener(pinaxcms_contents_events_Menu::INVALIDATE_SITEMAP, $this);
    }



    function _init()
    {
        pinax_ObjectValues::set('org.pinax', 'siteId', __Config::get('pinax.multisite.id'));
        parent::_init();

        // inizialize the editing language
        $language = __Session::get('pinax.editingLanguageId');
        if (is_null($language))
        {
            $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
            $ar->language_isDefault = 1;
            if ($ar->find()) {
                __Session::set('pinax.editingLanguage', $ar->language_code);
                __Session::set('pinax.editingLanguageId', $ar->language_id);
                __Session::set('pinax.editingLanguageIsDefault', $ar->language_isDefault);
                $language = $ar->language_id;
            } else {
                throw pinaxcms_core_application_ApplicationException::notDefaultLanguage('Default language not defined');
            }
        }

        pinax_ObjectValues::set('org.pinax', 'editingLanguageId', $language);

        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $languagesId = array();

        foreach ($it as $ar) {
           $languagesId[] = $ar->language_id;
        }

        pinax_ObjectValues::set('org.pinax', 'languagesId', $languagesId);
    }

    function _startProcess($readPageId=true)
    {
        $this->hostApplicationToAdmin = preg_replace('/\/admin$/', '', PNX_HOST);

        if  ($readPageId) {
            $this->_readPageId();
            $evt = array('type' => PNX_EVT_BEFORE_CREATE_PAGE);
            $this->dispatchEvent($evt);
        }

        $currentLanguage = __Session::get('pinax.language', __Config::get('DEFAULT_LANGUAGE'));
        if ($this->_user->language && $currentLanguage!==$this->_user->language) {
            __Session::set('pinax.language', $this->_user->language);
            pinax_helpers_Navigation::goHere();
        }

        parent::_startProcess(false);
    }

    function render_onStart()
    {
        $this->addJSLibCore();

        if (!pinax_ObjectValues::get('pinax.JS.Core', 'add', false) &&
            $this->config->get( 'PINAX_ADD_CORE_JS' ) &&
            $this->config->get( 'pinaxcms.session.check.enabled' ) &&
            __Session::get('pinax.userLogged')) {
                $this->_rootComponent->addOutputCode(pinax_helpers_JS::JScode('Pinax.sessionCheck("' . $this->config->get('pinaxcms.session.check.url') .'","'.$this->config->get('pinaxcms.session.check.interval').'");'), 'head');
        }
    }

    function _loadLocale()
    {
        // importa i file di localizzazione
        if (file_exists(pinax_Paths::getRealPath('PINAX_CMS_CLASSES').'pinaxcms/locale/'.$this->getLanguage().'.php'))
        {
            require_once(pinax_Paths::getRealPath('PINAX_CMS_CLASSES').'pinaxcms/locale/'.$this->getLanguage().'.php');
        }
        else
        {
            require_once(pinax_Paths::getRealPath('PINAX_CMS_CLASSES').'pinaxcms/locale/en.php');
        }
        parent::_loadLocale();
    }

    function switchEditingLanguage($id)
    {
        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
        $ar->load($id);
        __Session::set('pinax.editingLanguage', $ar->language_code);
        __Session::set('pinax.editingLanguageId', $ar->language_id);
        __Session::set('pinax.editingLanguageIsDefault', $ar->language_isDefault);
        pinax_ObjectValues::set('org.pinax', 'editingLanguageId', $ar->language_id);
    }

    function getEditingLanguageId()
    {
        return __Session::get('pinax.editingLanguageId');
    }

    function getEditingLanguage()
    {
        return __Session::get('pinax.editingLanguage');
    }

    function getEditingLanguageIsDefault()
    {
        return __Session::get('pinax.editingLanguageIsDefault');
    }

    function getPathApplicationToAdmin()
    {
        return $this->_pathApplicationToAdmin;
    }

    function getLanguageId()
    {
        return 1;
    }

    function getLanguage()
    {
        return !empty($this->_user->language) ? $this->_user->language : parent::getLanguage();
    }

    function isAdmin()
    {
        return true;
    }

    public function onInvalidateSitemap()
    {
        $siteMap = pinax_ObjectFactory::createObject('pinaxcms.core.application.SiteMapDB');
        $siteMap->invalidate();

        $siteMap = pinax_ObjectFactory::createObject('pinax.compilers.Routing', __Paths::getRealPath('APPLICATION_TO_ADMIN_CACHE'));
        $siteMap->invalidate();
    }
}
