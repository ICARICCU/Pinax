<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_application_SiteMapNode extends PinaxObject
{
	/**
	 * @var pinax_application_SiteMap
	 */
	private $_treeManager;

	/**
	 * @var array
	 */
	private $attributes;

	/**
	 * @var string|int
	 */
	public $id;

	/**
	 * @var string|int
	 */
	public $parentId;

	/**
	 * @var string
	 */
	public $pageType;

	/**
	 * @var bool
	 */
	public $isPublished;

	/**
	 * @var bool
	 */
	public $isVisible;

	/**
	 * @var bool|null
	 */
	public $hideByAcl;

	/**
	 * @var bool|null
	 */
	public $hideInNavigation;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $titleLink;

	/**
	 * @var null|string
	 */
	public $linkDescription;

	/**
	 * @var int
	 */
	public $order;

	/**
	 * @var bool|null
	 */
	public $hasPreview;

	/**
	 * @var int
	 */
	public $depth;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var bool|null
	 */
	public $isLocked;

	/**
	 * @var string
	 */
	public $creationDate;

	/**
	 * @var string
	 */
	public $modificationDate;

	/**
	 * @var bool|null
	 */
	public $showTitle;

	/**
	 * @var string
	 */
	public $moduleClass;

	/**
	 * @var string|null
	 */
	public $url = NULL;

	/**
	 * @var bool|null
	 */
	public $select = NULL;

	/**
	 * @var bool|null
	 */
	public $hasComment;

	/**
	 * @var bool
	 */
	public $printPdf;

	/**
	 * @var string|null
	 */
	public $cssClass;

	/**
	 * @var string|null
	 */
	public $icon;

	/**
	 * @var string|null
	 */
    public $extendsPermissions;

	/**
	 * @var string
	 */
	public $keywords = '';

	/**
	 * @var string
	 */
	public $description = '';

	/**
	 * @var string
	 */
	public $seoTitle = '';

	/**
	 * @var array|null
	 */
	public $extraData = null;

	function __construct(&$parent, &$node)
	{
		$this->_treeManager 		= &$parent;
		$this->attributes 			= &$node;
		$this->id 					= $this->attributes['id'];
		$this->parentId 			= $this->attributes['parentId'];
		$this->pageType 			= $this->attributes['pageType'];
		$this->title 				= javascript_to_html($this->attributes['title']);
		$this->titleLink 			= javascript_to_html($this->attributes['titleLink']);
		$this->order 				= $this->attributes['order'];
		$this->isVisible 			= $this->attributes['isVisible'];
		$this->hideByAcl            = $this->attributes['hideByAcl'];
		$this->hideInNavigation     = $this->attributes['hideInNavigation'];
		$this->depth 				= $this->attributes['depth'];
		$this->type 				= $this->attributes['type'];
		$this->isLocked 			= $this->attributes['isLocked'];
		$this->creationDate 		= $this->attributes['creationDate'];
		$this->modificationDate 	= $this->attributes['modificationDate'];
		$this->showTitle 			= $this->attributes['showTitle'];
		$this->hasComment 			= $this->attributes['hasComment'];
		$this->printPdf 			= $this->attributes['printPdf'] == 1;
		$this->cssClass 			= $this->attributes['cssClass'];
		$this->keywords 			= $this->attributes['keywords'];
		$this->description 			= $this->attributes['description'];
		$this->seoTitle 			= $this->attributes['seoTitle'];
		$this->icon 				= @$this->attributes['icon'];
		if (isset($this->attributes['linkDescription'])) $this->linkDescription = javascript_to_html($this->attributes['linkDescription']);
		if (isset($this->attributes['moduleClass'])) $this->url = $this->attributes['moduleClass'];
		if (isset($this->attributes['extendsPermissions'])) $this->url = $this->attributes['extendsPermissions'];
		if (isset($this->attributes['url'])) $this->url = $this->attributes['url'];
		if (isset($this->attributes['select'])) $this->select = $this->attributes['select'];
	}


	/**
	 * @return bool
	 */
	public function hasChildNodes()
	{
		return count($this->attributes['childNodes'])>0;
	}

	/**
	 * @param boolean $onlyVisible
	 * @param boolean $onlyPage
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &firstChild($onlyVisible=false, $onlyPage=true)
	{
		if (!$this->hasChildNodes()) return null;

		$menu = null;
		foreach($this->attributes['childNodes'] as $id) {
			$menu = $this->_treeManager->getNodeById($id);
			if ($onlyPage && $menu->type=='BLOCK') {
				continue;
			}
			if (!$onlyVisible || $menu->isVisible) {
				break;
			}
			$menu = null;
		}

		if ($menu && $onlyPage && $menu->type=='BLOCK') {
			$menu = null;
		}
		return $menu;
	}

	/**
	 * @return pinax_application_SiteMapNode[]
	 */
	public function &childNodes()
	{
		$childNodes = array();
		if ($this->hasChildNodes())
		{
			foreach ($this->attributes['childNodes'] as $id)
			{
				$childNodes[] = $this->_treeManager->getNodeById($id);
			}
		}

		return $childNodes;
	}

	/**
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &parentNode()
	{
		return $this->_treeManager->getNodeById($this->parentId);
	}

	/**
	 * @param int $depth
	 *
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &parentNodeByDepth($depth)
	{
		$r = NULL;
		if ($this->depth<$depth)
		{
			return $r;
		}
		else if ($this->depth==$depth)
		{
			return $this;
		}
		else
		{
			$menu = &$this;
			while (true)
			{
				$tempNode = &$menu->parentNode();
				if ($tempNode->depth==$depth)
				{
					return $tempNode;
				}
				else if ($tempNode->depth==0)
				{
					return $r;
				}

				$menu = &$tempNode;
			}
		}
	}

	/**
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &nextSibling()
	{
		$nullNode = null;
    	if ($this->parentId === 0) return $nullNode;
		$parentNode = $this->_treeManager->getNodeById($this->parentId);
		if ($parentNode === NULL) return $nullNode;
		$childNodes = &$parentNode->attributes['childNodes'];
		if ($childNodes === NULL) return $nullNode;
        $pos = array_search($this->id, $childNodes);

        if ($pos<count($childNodes)-1)
		{
			return $this->_treeManager->getNodeById($childNodes[++$pos]);
		}
		else
		{
			return $nullNode;
		}
	}

	/**
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &previousSibling()
	{
		$nullNode = null;
		if ($this->parentId === 0) return $nullNode;
		$parentNode = $this->_treeManager->getNodeById($this->parentId);
		if ($parentNode === NULL) return $nullNode;
		$childNodes = &$parentNode->attributes['childNodes'];
	    if ($childNodes === NULL) return $nullNode;
        $pos = array_search($this->id, $childNodes);

        if ($pos>0)
		{
			return $this->_treeManager->getNodeById($childNodes[--$pos]);
		}
		else
		{
			return $nullNode;
		}
	}

	/**
	 * @param string $attribute
	 * @return mixed|null
	 */
	public function getAttribute($attribute)
	{
		return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
	}

	/**
	 * @return pinax_application_SiteMap
	 */
	public function getSiteMap()
	{
		return $this->_treeManager;
	}
}
