<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_ConfigValue extends pinax_components_Component
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
		$this->defineAttribute('key',		true, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('action',	false, 	'get',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		$this->doLater($this, 'preProcess');
		parent::init();
	}

	function preProcess()
	{
		if ($this->getAttribute('action')=='set')
		{
			$value = is_null($this->getAttribute('value')) ? $this->getContent() : $this->getAttribute('value');
			__Config::set( $this->getAttribute('key'), $value );
		}
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		if ($this->getAttribute('action')=='get')
		{
			$this->addOutputCode(__Config::get($this->getAttribute('key')));
		}
	}
}
