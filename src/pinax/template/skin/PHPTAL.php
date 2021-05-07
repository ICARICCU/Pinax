<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_template_skin_PHPTAL extends pinax_template_skin_Skin
{
	function __construct($fileName, $skinFolders, $defaultHtml='', $language='')
	{
		parent::__construct($fileName, $skinFolders, $defaultHtml, $language);
		$this->_templClass = new PHPTAL();

		$this->_templClass->setPhpCodeDestination(pinax_Paths::getRealPath('CACHE'))
				->setTemplate($this->filePath.$this->fileName)
				->setForceReparse(false)
				->setTemplateRepository($skinFolders)
				->setEncoding( __Config::get('CHARSET'));
	}

	function set($theBlock, $theValue)
	{
		$this->_templClass->set($theBlock, $theValue);
	}

	function execute()
	{
		$res = $this->_templClass->execute();
		return pinax_helpers_Locale::replace($res);
	}
}
