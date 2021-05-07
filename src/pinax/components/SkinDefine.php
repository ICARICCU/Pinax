<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_SkinDefine extends pinax_components_Component
{
	var $_templateString;
	var $_skinType;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('skinType',				false, NULL, 	COMPONENT_TYPE_STRING);
		parent::init();
	}



	function process()
	{
		$this->_skinType = $this->getAttribute('skinType');
		if (is_null($this->_skinType))
		{
			$root = &$this->getRootComponent();
			$this->_skinType = $root->getAttribute('skinType');
			$this->setAttribute('skinType', $this->_skinType);
		}
		$this->_templateString 	= $this->getText();
	}

	function getTemplateString()
	{
		if ($this->getAttribute('skinType')=='PHPTAL')
		{
			$this->_templateString = str_replace('&gt;![CDATA[', '<![CDATA[', $this->_templateString);
			$this->_templateString = str_replace(']]&lt;', ']]>', $this->_templateString);
			return '<span tal:omit-tag="">'.$this->_templateString.'</span>';
		}
		else
		{
			return $this->_templateString;
		}
	}
}
