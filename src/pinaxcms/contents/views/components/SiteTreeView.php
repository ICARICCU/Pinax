<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_views_components_SiteTreeView  extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	public function init()
	{
		$this->defineAttribute('addLabel', false, '{i18n:pinaxcms.Add Page}', COMPONENT_TYPE_STRING);
		$this->defineAttribute('icon', false, __Config::get('pinax.icon.add'),	COMPONENT_TYPE_STRING);
		$this->defineAttribute('linkCssClass', false, __Config::get('pinax.icon.add'),	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title', false, '{i18n:pinaxcms.Site Structure}',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	public function process() {
		$this->_content =  new pinaxcms_contents_views_components_SiteTreeViewVO();
		$this->_content->addLabel = $this->getAttribute('addLabel');
		$this->_content->title = $this->getAttribute('title');
		$this->_content->ajaxUrl = $this->getAjaxUrl();
		$this->_content->addUrl = $this->_user->acl($this->_application->getPageId(), 'new') ?
			__Routing::makeUrl('linkChangeAction', array('action' => 'add')) :
			'';
		$this->_content->dnd = $this->_user->acl($this->_application->getPageId(), 'all') || $this->_user->acl($this->_application->getPageId(), 'publish') ? 'true' : 'false';
		$this->_content->linkCssClass = $this->getAttribute('linkCssClass');
		$this->_content->icon = $this->getAttribute('icon');

		if (!pinax_ObjectValues::get('pinaxcms.js', 'jsTree', false))
		{
			pinax_ObjectValues::set('pinaxcms.js', 'jsTree', true);

			// $this->dispatchEvent(pinax_events_Resources::addResource('js', '{path:STATIC_DIR}jquery/jquery-jstree/jquery.jstree.js', 'content'));
			// $this->dispatchEvent(pinax_events_Resources::addResource('js', '{path:STATIC_DIR}jquery/jquery-jstree/jquery.cookie.js', 'content'));

			$this->getRootComponent()->addOutputCode( pinax_helpers_JS::linkStaticJSfile( 'jquery/jquery-jstree/jquery.jstree.js' ) );
			$this->getRootComponent()->addOutputCode( pinax_helpers_JS::linkStaticJSfile( 'jquery/jquery-jstree/jquery.cookie.js' ) );
		}
	}
}

class pinaxcms_contents_views_components_SiteTreeViewVO
{
	public $addLabel;
	public $title;
	public $ajaxUrl;
	public $addUrl;
	public $icon;
	public $linkCssClass;
}

class pinaxcms_contents_views_components_SiteTreeView_render extends pinax_components_render_Render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<div id="treeview">
	<div id="treeview-title">
		<a id="js-pinaxcmsSiteTreeAdd" tal:condition="Component/addUrl" tal:attributes="href Component/addUrl; class Component/linkCssClass"><i class="icon-plus"></i> <tal:block tal:content="Component/addLabel" /></a>
		<h3 tal:content="Component/title"></h3>
	</div>
	<div id="treeview-inner">
		<div id="js-pinaxcmsSiteTree" tal:attributes="data-ajaxurl Component/ajaxUrl; data-dnd Component/dnd"></div>
	</div>
	<div id="openclose">
		<i class="icon-chevron-left"></i>
	</div>
</div>
EOD;
		return $skin;
	}
}
