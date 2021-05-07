<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Breadcrumbs extends pinax_components_Component
{
	protected $extraItem = null;
	protected $extraNodes = null;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('label',	false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('separator',	false, 	' &gt; ',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassLink', false, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssCurrent',	false, 	'current', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('menuId',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('drawIconLevel',		false, 	0,	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('trim',	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('trimLength',	false,	__Config::get('pinax.breadcrumbs.trimLength'),	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('trimElli',	false,	__Config::get('pinax.breadcrumbs.trimElli'), COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();

		$this->addEventListener(PNX_EVT_BREADCRUMBS_ADD, $this);
		$this->addEventListener(PNX_EVT_BREADCRUMBS_UPDATE, $this);
		$this->addEventListener(PNX_EVT_SITEMAP_UPDATE, $this);
	}

	function process()
	{
		$this->_content = new pinax_components_BreadcrumbsVO();
		$this->_content->label = $this->getAttribute('label');
		$this->_content->separator = $this->getAttribute('separator');
		$this->_content->cssClass = $this->getAttribute('cssClass');
		$drawIconLevel  = $this->getAttribute('drawIconLevel');
		$menuId 		= $this->getAttribute('menuId');
		$siteMap 		= &$this->_application->getSiteMap();
		$cssClassLink	= $this->getAttribute('cssClassLink');
		$currentMenu 	= is_null($menuId) ? $this->_application->getCurrentMenu() : $siteMap->getNodeById($menuId);
		$trimLength = $this->getAttribute('trim') ? $this->getAttribute('trimLength') : null;
		$trimElli = $this->getAttribute('trimElli');

		if ($this->extraItem) {
			array_unshift($this->_content->records, '<span class="'.$this->getAttribute('cssCurrent').'">'.$this->trim($this->extraItem, $trimLength, $trimElli).'</span>');
		}

		while (true) {
			$skipCurrent = false;
			$nodeTitle = empty($currentMenu->titleLink) ? $currentMenu->title : $currentMenu->titleLink;
			if ($currentMenu->type!='SYSTEM' && $nodeTitle) {
				$nodeDescription = empty($currentMenu->linkDescription) ? $nodeTitle: $currentMenu->linkDescription;
				$skipCurrent = $this->addExtraItems($currentMenu);

				if (count($this->_content->records)) {
					$icon = $currentMenu->depth<=$drawIconLevel && $currentMenu->icon ? $currentMenu->icon : '';
					if (!$skipCurrent) {
						if (empty($currentMenu->url)) {
							array_unshift($this->_content->records, pinax_helpers_Link::makeLink('link', array(
										'pageId' => $currentMenu->id,
										'title' => $nodeDescription,
										'label' => $this->trim($nodeTitle, $trimLength, $trimElli),
										'icon' => $icon,
										'cssClass' => $cssClassLink)));
						} else {
							array_unshift($this->_content->records, pinax_helpers_Link::makeSimpleLink($this->trim($nodeTitle, $trimLength, $trimElli), $currentMenu->url, $nodeDescription, $cssClassLink, '', array('icon' => $icon)));
						}
					}
				} else {
					array_unshift($this->_content->records, '<span class="'.$this->getAttribute('cssCurrent').'">'.$this->trim($nodeTitle, $trimLength, $trimElli).'</span>');
				}
			}
			if ($currentMenu->parentId===0 || !$currentMenu->parentId) break;
			$tempNode = &$currentMenu->parentNode();
			$currentMenu = &$tempNode;
		}
	}


	function siteMapUpdate($event)
	{
		if (!is_null($event->data)) {
			$this->setAttribute('menuId', $event->data);
		}
		$this->process();
	}

	function onBreadcrumbsAdd($event)
	{
		$this->extraNodes = $event->data;
		$this->process();
	}

	function onBreadcrumbsUpdate($event)
	{
		$this->extraItem = $event->data;
		$this->process();
	}

	/**
	 * @param array $currentMenu
	 * @return boolean
	 */
	private function addExtraItems($currentMenu)
	{
		if (empty($this->extraNodes)) return false;
		$skipCurrent = false;

		foreach ($this->extraNodes as $k => $extraNode) {
			if($extraNode['depth'] == $currentMenu->depth || !$extraNode['depth']) {
				array_unshift($this->_content->records, $extraNode['node']);

				if (isset($extraNode['remove']) && $extraNode['remove']) {
					$skipCurrent = true;
				}
			}
		}

		return $skipCurrent;
	}

	/**
	 * @param  string $value
	 * @param  int $length
	 * @param  string $elli
	 * @return string
	 */
	private function trim($value, $length, $elli)
	{
		return $length ? pinax_strtrim($value, $length, $elli) : $value;
	}
}

class pinax_components_BreadcrumbsVO
{
	var $separator = '';
	var $label = '';
	var $cssClass = '';
	var $records = array();
}


class pinax_components_Breadcrumbs_render extends pinax_components_render_Render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<ul tal:condition="php: count(Component.records)" tal:attributes="class Component/cssClass">
	<li tal:condition="Component/label" tal:content="Component/label" />
	<li tal:repeat="item Component/records"><span tal:omit-tag="" tal:content="structure item" /><span tal:condition="not: repeat/item/end" tal:content="structure Component/separator" /></li>
</ul>
EOD;
		return $skin;
	}
}
