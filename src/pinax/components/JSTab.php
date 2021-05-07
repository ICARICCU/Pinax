<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_JSTab extends pinax_components_ComponentContainer
{
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass', false, __Config::get('pinax.jstab.tab'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassTab', false, __Config::get('pinax.jstab.content'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('disabled', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('dropdown', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('routeUrl', false, NULL, COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html_onStart()
	{
		$this->addOutputCode('<div class="'.$this->getAttribute('cssClass').'" id="'.$this->getId().'">');
	}

	function render_html_onEnd()
	{
		$this->addOutputCode('</div>');
	}
}
