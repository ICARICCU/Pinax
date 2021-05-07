<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_LinkTo extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapTag',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',	false, 	NULL,	COMPONENT_TYPE_STRING);

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
		$this->_content = $this->_parent->loadContent($this->getId());

		if ($this->_parent->_tagname=='pnx:Page')
		{
			header('Content-Length: 0');
			header('Location: '.$this->_content );
			exit();
		}
	}

	function render_html()
	{
		$this->addOutputCode( $this->_render() );
	}

	function getContent()
	{
		return $this->_render();
	}

	function _render()
	{
		if ( !empty(  $this->_content ) )
		{
			$cssClass = $this->getAttribute('cssClass');
			$wrapStart = '';
			$wrapEnd = '';
			if (!is_null($this->getAttribute('wrapTag')))
			{
				if (!is_null( $cssClass ) )
				{
					$wrapperCssClass = ' class="'.$cssClass.'"';
				}
				$wrapStart = '<'.$this->getAttribute('wrapTag').$wrapperCssClass.'>';
				$wrapEnd = '</'.$this->getAttribute('wrapTag').'>';
			}

			$url = $this->_content;
			$label = $url;
			if (intval($url)) {
				// link interno
				$siteMap = $this->_application->getSiteMap();
				$menu = $siteMap->getNodeById($url);
				$label = $menu->title;
				$url = pinax_helpers_Link::makeURL('link', array('pageId' => $url));
			}

			$output = pinax_helpers_Link::formatLink( $url, $label, NULL, $cssClass );
			return $wrapStart.$output.$wrapEnd;
		}

		return '';
	}

	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['xmlns:pnx'] = "pinax.components.*";

		if (count($node->attributes))
		{
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->prefix=="adm")
				{
					$attributes[$attr->name] = $attr->value;
				}
			}
		}

		return pinax_helpers_Html::renderTag('pnx:Input', $attributes);
	}
}
