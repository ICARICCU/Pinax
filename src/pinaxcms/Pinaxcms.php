<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_Pinaxcms extends PinaxObject
{
    const VERSION = '3.0.1beta';

    // costanti mantenute per retrocompatibilità
    const FULLTEXT_DELIMITER = ' ## ';
    const FULLTEXT_MIN_CHAR = 2;
    private static $mediaArchiveBridge;

	public static function init()
	{
	    if (__Config::get('QUERY_CACHING_INIT')) {
            pinax_dataAccessDoctrine_DataAccess::initCache();
        }

	    pinax_loadLocale( 'pinaxcms.*' );
		pinax_loadLocale( 'pinaxcms.contents.*' );
        pinax_loadLocale( 'pinaxcms.languages.*' );

        $application = pinax_ObjectValues::get('org.pinax', 'application' );

        if ($application) {
            if (is_a($application, 'pinaxcms_core_application_Application')) {
                pinax_ObjectFactory::remapClass('pinax.components.Page', 'pinaxcms.views.components.Page');
                pinax_ObjectFactory::remapClass('pinax.mvc.components.Page', 'pinaxcms.views.components.MvcPage');

                pinaxcms_userManager_fe_Module::registerModule();
            }
            // la creazione dell'istanza serve per il listener
            $speakingUrlManager = $application->registerProxy('pinaxcms.speakingUrl.Manager');

            // registra il resolver di default
            pinaxcms_speakingUrl_Manager::registerResolver(pinax_ObjectFactory::createObject('pinaxcms.speakingUrl.PageResolver'));
        }

        self::extendConfig();

        self::$mediaArchiveBridge = pinax_ObjectFactory::createObject(__Config::get('pinaxcms.mediaArchive.bridge'));

        if ($application && __Config::get('pinaxcms.mediaArchive.mediaMappingEnabled')) {
            $application->registerProxy('pinaxcms.mediaArchive.services.MediaMappingService');
        }
	}


    static public function getSiteTemplatePath()
    {
        $templateName = __Config::get('pinaxcms.template.default');
        if (__Config::get('pinaxcms.contents.templateEnabled')) {
            $templateName = pinax_Registry::get( __Config::get( 'REGISTRY_TEMPLATE_NAME' ), $templateName);
         }

        $templatePath = __Paths::get( 'TEMPLATE_FOLDER' );
        if ( empty( $templatePath ) ) {
            // TODO verificare perché il path è sbagliato ed è necessartio mettere ../
            $templatePath = __Paths::get( 'APPLICATION_STATIC' ).'templates/';
        }
        $templatePath .= $templateName;
        return '../'.$templatePath;
    }

    /**
     * @return pinaxcms_mediaArchive_BridgeInterface
     */
    static public function getMediaArchiveBridge()
    {
        return self::$mediaArchiveBridge;
    }

    /**
     * @return void
     */
    static public function setMediaArchiveBridge($bridge)
    {
        self::$mediaArchiveBridge = $bridge;
    }

    static private function extendConfig()
    {
        $config = [
            'pinaxcms.sitemap.cacheLife' => 36000,
            'pinaxcms.content.history' => true,
            'pinaxcms.content.history.comment' => false,
            'pinaxcms.content.fulltext.minchar' => 2,
            'pinaxcms.content.fulltext.delimiter' => ' ## ',
            'pinax.dataAccess.document.enableComment' => '{{pinaxcms.content.history.comment}}',
            'pinaxcms.pageEdit.editUrlEnabled' => true,
            'pinaxcms.mediaArchive.exifEnabled' => false,
            'pinaxcms.mediaArchive.addFromZipEnabled' => false,
            'pinaxcms.speakingUrl' => false,
            'pinaxcms.content.showAllPageTypes' => true,
            'pinaxcms.form.actionLink.cssClass' => 'btn action-link',
            'pinaxcms.icon.add.cssClass' => 'icon-plus',
            'pinaxcms.icon.delete.cssClass' => 'icon-trash',
            'pinaxcms.formElement.boxIcon.cssClass' => 'entities big ico-box span11',
            'pinaxcms.print.enabled' => 'false',
            'pinaxcms.print.pdf.enabled' => 'false',
            'pinaxcms.mediaArchive.bridge' => 'pinaxcms.mediaArchive.Bridge',
            'pinaxcms.content.draft' => false,
            'pinaxcms.autocompletePagePicker.limit' => 10,
            'pinaxcms.dublincore.enabled' => false,
            'pinaxcms.contents.templateEnabled' => false,
            'pinaxcms.mobile.template.enabled' => false,
            'pinaxcms.pagePicker.queryStringEnabled' => true,
            'pinaxcms.contentsedit.default.acl' => true,
            'pinaxcms.duplicatePage.visibility' => 0,
            'pinaxcms.metadata.showSiteNameInTitle' => true,
            'pinaxcms.home.autoRedirect' => true,
            'pinaxcms.session.check.enabled' => false,
            'pinaxcms.session.check.url' => '/rest/sessionCheck',
            'pinaxcms.session.check.interval' => 30000,
            'pinaxcms.session.check.return.url' => '',
            'pinaxcms.duplicationMenuStorageDelegate' => 'pinaxcms.contents.utils.DuplicationMenuStorageDelegate',
            'pinaxcms.tinyMCE.link.controllerName' => 'pinaxcms.contents.controllers.tinymce.ajax.LinkList'
        ];

        foreach($config as $k=>$v) {
            if (!__Config::exists($k)) {
                __Config::set($k, $v);
            }
        }
    }
}
