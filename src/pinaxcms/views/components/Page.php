<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinaxcms_views_components_Page extends pinax_components_Page
{
	private $templateData;
	private $customTemplate;
	private $selfId;

	protected $menu;
	protected $siteProp;


	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('allowBlocks',	false, false, 	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		if (!$this->_application->canViewPage() || !$this->checkAcl()) {
			pinax_helpers_Navigation::accessDenied($this->_user->isLogged());
		}

		$this->selfId = $this->getId();
		$this->_content = array();

		$this->loadMenuAndSiteProps();
		$this->checkRedirectUrl($this->menu->url);
		$this->loadContentFromDB();
		$this->loadTemplate();

		$this->processChilds();
	}


	function render($outputMode = NULL, $skipChilds = false)
	{
		$this->renderPageProperties($this->menu, $this->siteProp['title']);
		$this->renderSiteProperties($this->siteProp);

		if (is_object($this->customTemplate)) {
			$this->customTemplate->render($this->_application, $this, $this->templateData);
		}
		return parent::render();
	}


	function loadContent($id, $bindTo = '')
	{
		if (property_exists($this->_content, $id)) {
			return $this->_content->{$id};
		} else if (strpos($id, 'template:')===0) {
			$id = substr($id, strlen('template:'));
		} else if (strpos($id, $this->selfId)===0) {
			$id = substr($id, strlen($this->selfId)+1);
		} else {
			return '';
		}

		return property_exists($this->templateData, $id) ? $this->templateData->{$id} : '';
	}


	protected function loadContentFromDB()
	{

		// if ($this->_user->backEndAccess && pinax_Request::get( 'draft', '' ) == '1')
		// {
		// 	$versionStatus = 'DRAFT';
		// }
// TODO gestire lo stato PUBLISHED E DRAFT
		$languageId = pinax_ObjectValues::get('org.pinax', 'languageId');
		$pageId = $this->_application->getPageId();

		$contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ContentProxy');
		$this->_content = $contentProxy->readContentFromMenu($pageId, $languageId);

		if (!$this->_content->__isTranslated) {
			__ObjectValues::set('org.pinax', 'translationNotAvailable', true);

			if (__Config::get('pinaxcms.content.defaultLanguageIfNotAvailable')) {
				$languageProxy = __ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
				$defaultLanguageId = $languageProxy->getDefaultLanguageId();

				if ($this->_content->__languageId != $defaultLanguageId) {
					$this->_content = $contentProxy->readContentFromMenu($pageId, $defaultLanguageId);
				}
			}
		}
	}

	protected function loadTemplate()
	{
		$templateProxy = pinax_ObjectFactory::createObject('pinaxcms.template.models.proxy.TemplateProxy');
		$this->customTemplate = $templateProxy->getTemplateCustomClass();

		if (__Config::get('pinaxcms.contents.templateEnabled')) {
	        $templateName = $templateProxy->getSelectedTemplate();
	        $templatePath = $templateProxy->getTemplateRealpath();
	        $this->templateData = $templateProxy->getDataForMenu($this->_application->getPageId());

			// if is defined a custom XML file read and attach to component DOM
			if (file_exists($templatePath.'/Template.xml'))
			{
				pinax_ObjectFactory::attachPageToComponent(
	                $this,
	                $this->_application,
	                'Template',
	                $templateProxy->getTemplateRealpath(),
	                array(),
	                $this->selfId.'-',
	                false);
			}

			// check if there is a templateFileName override
			if (property_exists($this->templateData, 'templateFileName') && $this->templateData->templateFileName != 'default') {
				$this->setAttribute('templateFileName', $this->templateData->templateFileName);
			}

			if (is_object($this->customTemplate) && method_exists($this->customTemplate, 'process')) {
				$this->customTemplate->process($this->_application, $this, $this->templateData);
			}
		}
	}

	/**
	 * @param  SiteMapNode $menu
	 * @param  array $siteName
	 */
	protected function renderPageProperties($menu, $siteName)
	{
		$title = pinax_ObjectValues::get('pinax.og', 'title', $menu->seoTitle ? $menu->seoTitle : $menu->title );
		$description = pinax_ObjectValues::get('pinax.og', 'description', $menu->description );
        $keywords = pinax_ObjectValues::get('pinax.og', 'keywords', $menu->keywords );

        $pageTitle = __Config::get('pinaxcms.metadata.showSiteNameInTitle') ? $title.' - '.$siteName : $title;

		$this->addOutputCode(pinax_encodeOutput($pageTitle), 'docTitle');
        $this->addOutputCode($title, 'metadata_title');
        $this->addOutputCode($description, 'metadata_description');
        $this->addOutputCode($keywords, 'metadata_keywords');

		$reg = __T( strlen( $menu->creationDate ) <= 10 || preg_match('/00:00:00|12:00:00 AM/', $menu->creationDate) ? 'PNX_DATE_FORMAT' : 'PNX_DATETIME_FORMAT' );
		$updateText= pinax_locale_Locale::get('MW_FOOTER',
													pinax_defaultDate2locale($reg, $menu->creationDate),
													pinax_defaultDate2locale($reg, $menu->modificationDate));
		$this->addOutputCode($updateText, 'docUpdate');
	}

	/**
	 * @param  array $siteProp
	 */
	protected function renderSiteProperties($siteProp)
	{
        $this->addOutputCode($siteProp['copyright'], 'copyright');
		$this->addOutputCode(pinax_helpers_Link::parseInternalLinks($siteProp['address']), 'address');

		$slideShowSpeed = ((int)$siteProp['slideShow'] ? :5)*1000;
		$this->addOutputCode( pinax_helpers_JS::JScode( 'if (typeof(Pinax)!=\'object\') Pinax = {}; Pinax.slideShowSpeed = '.$slideShowSpeed.';' ), 'head' );
	}

	/**
	 * @return null
	 */
	protected function loadMenuAndSiteProps()
	{
		$this->menu = $this->_application->getCurrentMenu();
		$this->siteProp = $this->_application->getSiteProperty();
	}

	/**
	 * @param  string $url
	 */
	protected function checkRedirectUrl($url)
	{
		if (strpos($url, 'http')===0) {
            pinax_helpers_Navigation::gotoUrl($url);
        }
	}
}
