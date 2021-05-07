<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinaxcms_views_components_Metadata extends pinax_components_Component
{
	public function init()
	{
		$this->defineAttribute('htmlVersion', false, 'xhtml1', COMPONENT_TYPE_STRING);

        parent::init();
	}

	public function render_html()
	{
		$menu = $this->_application->getCurrentmenu();
		$language = $this->_application->getLanguage();
		$title = pinax_ObjectValues::get('pinax.og', 'title', $menu->title );
		$description = pinax_ObjectValues::get('pinax.og', 'description', $menu->description );
		$image = pinax_ObjectValues::get('pinax.og', 'image');
        $keywords = pinax_ObjectValues::get('pinax.og', 'keywords', $menu->keywords );

		$this->addOutputCode(
			$this->renderMetadata(pinax_htmlentities($title), pinax_htmlentities($description), $image, pinax_htmlentities($keywords), $language)
		);

		if (__Config::get('pinaxcms.dublincore.enabled')) {
           $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
           $arMenu = $menuProxy->getMenuFromId($menu->id, $this->_application->getLanguageId());

			$this->addOutputCode(
				$this->renderDublinCore($arMenu, $language)
			);
		}
	}

	/**
	 * @param  string $description
	 * @param  strin $keywords
	 * @param  string $language
	 * @return string
	 */
	private function renderMetadata($title, $description, $image, $keywords, $language)
	{
		$metadata = "\n" . '<meta property="og:type" content="website">' . "\n";
		if ($this->getAttribute('htmlVersion') == 'xhtml1' and !empty($language)) {
			$metadata .= '<meta http-equiv="content-language" content="' . $language . '" />' . "\n";
		}

		if (!empty($title)) {
			$metadata .= '<meta property="og:title" content="' . $title . '" />' . "\n";
		}

		if (!empty($keywords)) {
			$metadata .= '<meta name="keywords" content="' . $keywords . '" />' . "\n";
		}

		if (!empty($description)) {
			$metadata .= '<meta name="description" content="' . $description . '" />' . "\n";
			$metadata .= '<meta property="og:description" content="' . $description . '" />' . "\n";
		}

		if (!empty($image)) {
			$metadata .= '<meta property="og:image" content="' . $image . '" />' . "\n";
			$metadata .= '<meta property="twitter:card" content="summary_large_image">' . "\n";
		}

		$canonical = __Routing::scriptUrl(true);
		if (!empty($canonical)) {
			$metadata .= '<link rel="canonical" href="' . $canonical . '" />' . "\n";
			$metadata .= '<meta property="og:url" content="' . $canonical . '" />';
		}

		return $metadata;
	}

	/**
	 * @param  pinax_dataAccessDoctrine_AbstractActiveRecord $menu
	 * @param  string $language
	 * @return string
	 */
	private function renderDublinCore($menu, $language)
	{
		$metadata = <<<EOD
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />
<meta name="DC.Title" content="{$menu->menudetail_title}" />
<meta name="DC.Creator" content="{$menu->menudetail_creator}" />
<meta name="DC.Subject" content="{$menu->menudetail_subject}" />
<meta name="DC.Description" content="{$menu->menudetail_description}" />
<meta name="DC.Publisher" content="{$menu->menudetail_publisher}" />
<meta name="DC.Contributor" content="{$menu->menudetail_contributor}" />
<meta name="DC.Date" content="(SCHEME=ISO8601) {$menu->menu_modificationDate}" />
<meta name="DC.Type" content="{$menu->menudetail_type}" />
<meta name="DC.Format" content="(SCHEME=IMT) text/html" />
<meta name="DC.Identifier" content="{$menu->menudetail_identifier}" />
<meta name="DC.Source" content="{$menu->menudetail_source}" />
<meta name="DC.Language" content="(SCHEME=ISO639-1) {$language}" />
<meta name="DC.Relation" content="{$menu->menudetail_relation}" />
<meta name="DC.Coverage" content="{$menu->menudetail_coverage}" />
<meta name="DC.Rights" content="" />
EOD;
		return $metadata;
	}
}
