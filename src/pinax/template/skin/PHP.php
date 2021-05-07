<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_template_skin_PHP extends pinax_template_skin_Skin
{
	private $_values;

	function __construct($fileName, $skinFolders, $defaultHtml='', $language='')
	{
		parent::__construct($fileName, $skinFolders, $defaultHtml, $language);
		$this->_values = array();
	}

	function set($theBlock, $theValue)
	{
		$this->_values[$theBlock] = $theValue;
	}

	function execute()
	{
		foreach (array_keys($this->_values) as $k)
		{
			$$k =& $this->_values[$k];
		}

		unset($k);
		ob_start();
		include($this->filePath.$this->fileName);
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
}
