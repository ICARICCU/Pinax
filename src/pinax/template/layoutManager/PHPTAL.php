<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_template_layoutManager_PHPTAL extends pinax_template_layoutManager_LayoutManager
{

	function apply(&$regionContent)
	{
		$this->checkRequiredValues( $regionContent );
		$templateSource = @implode('', file($this->fileName));
		$templateSource = $this->fixUrl( $templateSource );
		$compiler 			= pinax_ObjectFactory::createObject('pinax.compilers.Skin');
		$compiledFileName 	= $compiler->verify($this->fileName, array('defaultHtml' => $templateSource));

		$pathInfo = pathinfo($compiledFileName);
		$templClass = new PHPTAL($pathInfo['basename'], $pathInfo['dirname'], pinax_Paths::getRealPath('CACHE_CODE'));
		foreach ($regionContent as $region => $content)
		{
			$templClass->set($region,  $content);
		}

		try {
			$res = $templClass->execute();
			$templateSource = $res;
		} catch(Exception $e) {
			$templateSource = $e->getMessage();
		}

		if (isset($regionContent['__body__']))
		{
			$templateSource = $this->modifyBodyTag($regionContent['__body__'], $templateSource);
		}
		$templateSource = $this->fixLanguages( $templateSource );
		return $templateSource;
	}
}
