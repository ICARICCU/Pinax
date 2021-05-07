<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



class pinax_components_Caption extends pinax_components_Component
{
	var $_caption = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('accesskey',		false, 	NULL,	COMPONENT_TYPE_STRING);			// TODO
		$this->defineAttribute('crop',			false, 	NULL,	COMPONENT_TYPE_STRING);		// TODO
		$this->defineAttribute('image',			false, 	NULL,	COMPONENT_TYPE_STRING);		// TODO
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('tabindex',		false, 	NULL,	COMPONENT_TYPE_INTEGER);		// TODO

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->_content = $this->getAttribute('label');
	}

	function render_html()
	{
		if (!is_null($this->_content))
		{
			$output  = '<legend>'.$this->_content.'</legend>';
			$this->addOutputCode($output);
		}
	}
}
