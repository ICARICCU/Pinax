<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_NavigationMenu extends pinax_components_Component
{
	var $_currentMenuId;
	var $_currentMenu;
	var $_menuToOpen;
	var $_startMenu;
	var $_startDepth;
	var $_endDepth;

	private $skipBlock;

	/**
	 * @var pinax_components_interfaces_NavigationMenuFilter
	 */
	private $filterClass;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('attributeToSelect',	false, 	'id', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('selectLink',		false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssClass',			false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('nestedCssClass',	false, 	false, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassLi',		false, 	'', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('nestedCssClassLi',	false, 	'', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassA',			false, 	'', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('depth',				false, 	'+1', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('flat',				false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('forceLink',			false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('showPath',			false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('maxPathDepth',		false, 	NULL, 	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('selectParent',		false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('selectPath',		false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('startFrom', 		true, 	'*', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('startFromDepth',	false, 	NULL, 	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('showOnlyChilds',	false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('drawAllChilds',		false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('type',				false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',				false, 	'', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('headingTitle',		false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('headingCssClass',	false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('headingTitleLink',	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('wrapTag',			false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapTagCssClass',	false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('omitId',		false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('openFirstLevel',	false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssCurrent',	false, 	'current', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('itemIcon',			false, 	'', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('skipBlock',		false, 	true, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('filterClass',   false, '',     COMPONENT_TYPE_STRING);
		$this->defineAttribute('menuUrlRel',        false,     '',     COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();

		$this->addEventListener(PNX_EVT_SITEMAP_UPDATE, $this);
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		if (is_null($this->getAttribute('type')) || $this->getAttribute('type')==__Config::get('DATASOURCE_MODE'))
		{
			$siteMap 				= &$this->_application->getSiteMap();
			$this->_currentMenu 	= &$this->_application->getCurrentMenu();
		}
		else
		{
			switch ($this->getAttribute('type'))
			{
				case 'db':
					$siteMap = &pinax_ObjectFactory::createObject('pinax.application.SiteMapDB');
					break;
				case 'xml':
					$siteMap = &pinax_ObjectFactory::createObject('pinax.application.SiteMapXML');
					break;
				case 'pageType':
					$siteMap = &pinax_ObjectFactory::createObject('pinax.application.SiteMap');
					break;
			}
			$this->_currentMenu 	= &$siteMap->getHomeNode();
		}
		$this->_currentMenuId = $this->_currentMenu->id;
		$this->_startMenu = &$this->_currentMenu;
		// cerca il menu da dove iniziare a disegnare
		$startFrom = explode('-', $this->getAttribute('startFrom'));
		switch( $startFrom[0] )
		{
			case '*':
				break;
			case '*parent':
				if (count($startFrom)==1)
				{
					$this->_startMenu = &$this->_currentMenu->parentNode();
					if (is_null($this->_startMenu)) $this->_startMenu = &$this->_currentMenu;
				}
				else if ($this->_startMenu->depth>=$startFrom[1])
				{
					while (true)
					{
						if ($this->_startMenu->depth==$startFrom[1] || $this->_startMenu->depth<$startFrom[1])
						{
							//$startMenu = &$this->_currentMenu;
							break;
						}

						$tempNode = &$this->_startMenu->parentNode();
						if (is_null($tempNode) || $tempNode->depth==0)
						{
							// siamo arrivati alla home
							$this->_startMenu = NULL;
						}
						$this->_startMenu = &$tempNode;
					}
				}
				else
				{
					$this->setAttribute('visible', false);
					return;
				}
				break;
			default:
				// TODO
				// controllare che il men?? indicato sia corretto
				$this->_startMenu = &$siteMap->getNodeById($startFrom[0]);
				break;
		}

		// calcola i men?? da aprire
		$this->_menuToOpen = array();
		if ($this->getAttribute('showPath')===true)
		{
			$tempNode2 = $this->_application->getCurrentMenu();

			//do 16/04/2010
			if( $this->getAttribute('openFirstLevel') )
			{
				$menuHome = &$siteMap->getNodeById(1);
				foreach( $menuHome->attributes['childNodes'] as $childPageId )
				{
					$this->_menuToOpen[] = $childPageId;
				}
			}

			while (true)
			{
				if(!is_null($this->getAttribute('maxPathDepth')))
				{
					if ($tempNode2->depth<=$this->getAttribute('maxPathDepth')) $this->_menuToOpen[] = $tempNode2->id;
				}
				else
				{
					$this->_menuToOpen[] = $tempNode2->id;
				}

				$tempNode = &$tempNode2->parentNode();
				if (is_null($tempNode) || $tempNode->depth==0)
				{
					break;
				}
				$tempNode2 = &$tempNode;
			}
		}

		$this->_startDepth = is_null($this->getAttribute('startFromDepth')) ? $this->_startMenu->depth : $this->getAttribute('startFromDepth');
		$endDepth = $this->getAttribute('depth');
		if ($endDepth{0}=='+')
		{
			$this->_endDepth = $this->_startMenu->depth+intval(substr($endDepth, 1));
		}
		else
		{
			$this->_endDepth = intval($endDepth);
		}
		$this->_content = array();
	}


	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		// la creazione del tree viene fatta nel render
		// invece che nel process cos?? ?? possibile per gli altri componenti
		// cambiare dinamicamente la struttura del sito
		$this->makeNavigationMenu();
		$output = '';
		if (count($this->_content))
		{
			$this->_render_html($this->_content, $output);
			$tempOutput = '';

			$wrapTag = $this->getAttribute('wrapTag');
			if (!is_null($wrapTag))
			{
				$tempOutput = pinax_helpers_Html::renderTag($wrapTag, array('class' => $this->getAttribute('wrapTagCssClass'), false));
			}
			if (!is_null($this->getAttribute('headingTitle')))
			{
				$title = $this->getAttributeString('title');
				if ( empty( $title ) ) $title = $this->_startMenu->title;
				if ( $this->getAttribute('headingTitleLink') ) {
					$siteMap = $this->_application->getSiteMap();
					$hedingMenu = $siteMap->getNodeById($this->_startMenu->id);
					$headingTitle = empty($hedingMenu->titleLink) ? $hedingMenu->title : $hedingMenu->titleLink;
					$linkParams = array('pageId' => $hedingMenu->id,
										'title' => $headingTitle,
										'label' => $headingTitle);
					$headingUrl = pinax_helpers_Link::makeLink('link', $linkParams);

					$title = $headingUrl;
				}
				$tempOutput .= pinax_helpers_Html::renderTag($this->getAttribute('headingTitle'), array('class' => $this->getAttribute('headingCssClass')), true, $title );
			}
			$output = $tempOutput.$output;
			if (!is_null($wrapTag))
			{
				$output .= pinax_helpers_Html::closeTag($wrapTag);
			}
		}
		$this->addOutputCode($output);
	}

	function _render_html(&$menu, &$output)
	{
		$attributes = array();
		$attributes['class'] = $this->getAttribute('cssClass');

		$cssClassLi = $this->getAttribute('cssClassLi');
		if (empty($output))
		{
			if ( !$this->getAttribute( 'omitId' ) ) $attributes['id'] = $this->getId();
			$attributes['title'] = $this->getAttributeString('title');
			$output .=  pinax_helpers_Html::renderTag('ul', $attributes, false);
		}
		else
		{
			$nestedCssClass = $this->getAttribute('nestedCssClass');
			$attributes['class'] = $this->getAttribute('nestedCssClass')==='true' ? $attributes['class'] : $nestedCssClass;
			$output .=  pinax_helpers_Html::renderTag('ul', $attributes, false);
			$nestedCssClassLi = $this->getAttribute('nestedCssClassLi');
			$cssClassLi = $nestedCssClassLi ? $nestedCssClassLi : $cssClassLi;
		}

		for($i=0; $i<count($menu); $i++)
		{
			if (is_array($menu[$i]['node']))
			{
				$this->_render_html($menu[$i]['node'], $output);
				$output .= '</li>';
			}
			else
			{
				$cssClass = $menu[$i]['cssClass'].' '.$cssClassLi;
				$selected = $menu[$i]['selected'];
				if ($cssClass) {
					if ( strpos($selected, 'class="')!==false) {
						$selected = str_replace('class="', 'class="'.$cssClass.' ', $selected);
						$cssClass = '';
					}
					else
					{
						$cssClass = 'class="'.$cssClass.'"';
					}
				}
				$attributes = trim($cssClass.' '.$selected);
				$output .= '<li'.($attributes ? ' '.$attributes : '').'>'.$menu[$i]['node'].($i+1<count($menu) && is_array($menu[$i+1]['node']) ? '' : '</li>');
			}
		}
		$output .= '</ul>';
	}

	function makeNavigationMenu()
	{
		$this->skipBlock = $this->getAttribute('skipBlock');
		$filterClassName = $this->getAttribute('filterClass');
        $this->filterClass = $filterClassName ? pinax_ObjectFactory::createObject($filterClassName) : null;
        if ($this->filterClass) {
            if (!($this->filterClass instanceof pinax_components_interfaces_NavigationMenuFilter)) {
                throw pinax_exceptions_InterfaceException::notImplemented('pinax.components.interfaces.NavigationMenuFilter', $filterClassName);
            }
        }

		$this->_makeNavigationMenu($this->_startMenu, $this->_endDepth, $this->_content, $this->getAttribute('showOnlyChilds'));
	}

	function _makeNavigationMenu(&$node, $endDepth, &$menu, $skip=false)
	{
		$skip = !$skip ? $node->depth<$this->_startDepth : $skip;
		if (
				($node->type == 'BLOCK' && $this->skipBlock) ||
				$node->depth > $endDepth ||
				$node->isVisible==0 ||
				$node->hideInNavigation ||
				($node->type == 'SYSTEM' && !$skip) ||
				!$this->_application->canViewPage( $node->id )
			) return true;

		if (is_string($node->isVisible) && preg_match("/\{php\:.*\}/i", $node->isVisible)) {
			$phpcode = pinax_helpers_PhpScript::parse($node->isVisible);
			if (!eval($phpcode)) {
				return true;
			}
		}

		$nodeTitle = empty($node->titleLink) ? $node->title : $node->titleLink;
		$nodeDescription = empty($node->linkDescription) ? $nodeTitle: $node->linkDescription;
		$nodeIcon = $node->icon ? : $this->getAttribute('itemIcon');
		$cssClassA = $this->getAttribute('cssClassA');
		$menuNode = array();
		if (!$skip && $nodeTitle)
		{
			$menuSelected = false;
			$selectParent = $this->getAttribute('selectParent');
			$selectPath = $this->getAttribute('selectPath');
			if ( !$selectParent && !$selectPath) {
				$menuSelected = $this->_currentMenuId==$node->id;
			} else if ( $selectParent && !$selectPath) {
				$parentNode = &$this->_currentMenu->parentNodeByDepth($node->depth);
				if (is_object($parentNode) && $node->depth > 1)
				{
					$menuSelected = $parentNode->id==$node->id || $this->_currentMenuId==$node->id;
				}
			} else if (!$selectParent && $selectPath) {
				$menuSelected = in_array($node->id, $this->_menuToOpen);
			}

			$menuNode['id'] 	=  $node->id;
			$menuNode['title'] 	=  $nodeTitle;
			$menuNode['label'] 	=  $nodeDescription;
			$menuNode['depth'] 	=  $node->depth;
			$menuNode['parentId'] =  $node->parentId;
			$menuNode['type'] 	=  $node->pageType;
			$menuNode['cssClass'] =  $node->cssClass;
			$menuNode['selected'] = !$this->getAttribute('selectLink') && $menuSelected ? ' '.$this->getAttribute('attributeToSelect').'="'.$this->getAttribute('cssCurrent').'"' : '';
			$menuNode['haveChild'] =  $node->hasChildNodes();

			if ($this->_currentMenuId==$node->id && !$this->getAttribute('forceLink'))
			{
				$menuNode['node'] =  $nodeTitle;
			}
			else
			{
				$cssClass = $this->getAttribute('selectLink') && ($this->_currentMenuId==$node->id || $menuSelected)? $this->getAttribute('cssCurrent') : '';
				if ($node->type == 'BLOCK') {
					$parentNode = &$node->parentNode();

					$node->url = ($parentNode->url ? $parentNode->url : __Link::makeUrl('link', array('pageId' => $parentNode->id))).'#'.pinax_slugify($nodeTitle);
				}

				if (empty($node->url))
				{
					$linkParams = array('pageId' => $node->id,
										'title' => $nodeDescription,
										'label' => $nodeTitle,
										'cssClass' => trim($cssClass.' '.$cssClassA),
										'icon' => $nodeIcon );
					$menuNode['node'] = pinax_helpers_Link::makeLink('link', $linkParams);
				}
				else
				{
					$url = $node->url;
					if (strpos($url, 'route:')!==false) {
						$url = substr($url, 6);
						$url = __Routing::makeUrl($url, __Request::getAllAsArray());
					}
					$rel = strpos($url, PNX_HOST)===0 ? '' : $this->getAttribute('menuUrlRel');
					$menuNode['node'] =  pinax_helpers_Link::makeSimpleLink($nodeTitle, $url, $nodeDescription, trim($cssClass . ' ' . $cssClassA), $rel, array('icon' => $nodeIcon));

				}
			}
		}

		if ($this->filterClass) {
			$menuNode = $this->filterClass->filter($menuNode);
		}

		if ($menuNode && count($menuNode))
		{
			$menu[] = $menuNode;
		}

		if ($node->hasChildNodes())
		{
			$childNodes = &$node->childNodes();
			$tempMenu = array();
			for($i=0; $i<count($childNodes); $i++)
			{
				$newEndDepth = max($this->_endDepth+(in_array($node->id, $this->_menuToOpen) ? 1:0), $node->depth+(in_array($node->id, $this->_menuToOpen) ? 1:0));
				if ($this->getAttribute('showOnlyChilds'))
				{
					if (	in_array($node->id, $this->_menuToOpen) ||
							$this->_startMenu->depth==$node->depth ||
							$this->_currentMenuId==$node->id ||
							$this->getAttribute('drawAllChilds'))
					{
						$this->_makeNavigationMenu($childNodes[$i], $newEndDepth, $tempMenu);
					}
				}
				else
				{
					$this->_makeNavigationMenu($childNodes[$i], $newEndDepth, $tempMenu);
				}
			}
			if (count($tempMenu))
			{
				$menuNode['node'] = $tempMenu;
				$menuNode['selected'] = '';
				if ($this->getAttribute('flat') && count($menu))
				{
					$menu = array_merge($menu, $menuNode['node']);
				}
				else
				{
					$menu[] = $menuNode;
				}
			}
		}
		if ($skip && count($menu))
		{
			$menu = $menu[0]['node'];
		}
	}

	function siteMapUpdate()
	{
		$this->process();
	}
}
