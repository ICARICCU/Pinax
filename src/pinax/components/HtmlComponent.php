<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_HtmlComponent extends pinax_components_ComponentContainer
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('cssClass',	false, 	NULL,	COMPONENT_TYPE_STRING);

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
		$tagContent = $this->getText();
		if (empty($tagContent))
		{
			// richiede il contenuto al padre
			$tagContent = $this->_parent->loadContent($this->getId());
			$this->setText($tagContent);
		}

		$this->processChilds();
	}

	function getContent()
	{
		return pinax_encodeOutput($this->getText());
	}
}
