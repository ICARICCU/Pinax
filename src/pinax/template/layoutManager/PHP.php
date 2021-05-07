<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_template_layoutManager_PHP extends pinax_template_layoutManager_LayoutManager
{

	function apply(&$regionContent)
	{
		$this->checkRequiredValues( $regionContent );
		foreach ($regionContent as $k => $v)
		{
			if (!isset($$k)) $$k = $v;
			else $$k .= $v;
		}

		$compiler 			= pinax_ObjectFactory::createObject('pinax.compilers.LayoutManagerPHP');
		$compiledFileName 	= $compiler->verify( $this->fileName );

		if ( $compiledFileName  === false )
		{
			$templateSource = @implode('', file($this->fileName));
			$templateSource = $this->fixUrl( $templateSource );
			$compiledFileName = $compiler->compile( $templateSource );
		}

		ob_start();
		include($compiledFileName);
		$templateSource = ob_get_contents();
		ob_end_clean();

		if (isset($regionContent['__body__']))
		{
			$templateSource = $this->modifyBodyTag($regionContent['__body__'], $templateSource);
		}

		$templateSource = $this->fixLanguages( $templateSource );
		return $templateSource;
	}
}
