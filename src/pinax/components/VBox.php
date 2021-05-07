<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_VBox extends pinax_components_ComponentContainer
{

	function init()
	{
		$this->defineAttribute('cssClass', 	false, null, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title', 	false, null, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('titleTag', 	false, 'h3', 	COMPONENT_TYPE_STRING);

		parent::init();
	}


	function render_html_onStart()
	{
		$attributes 		 	= array();
		$attributes['id'] 	= $this->getId();
		$attributes['class'] 	= $this->getAttribute('cssClass');
		$output = '<div '.$this->_renderAttributes($attributes).'>';
		$title = $this->getAttributeString('title');
		if (!empty($title))
		{
			$output .= '<'.$this->getAttribute('titleTag').'>'.$title.'</'.$this->getAttribute('titleTag').'>';
		}
		$this->addOutputCode($output);
	}

	function render_html_onEnd()
	{
		$output  = '</div>';
		$this->addOutputCode($output);
	}
}
