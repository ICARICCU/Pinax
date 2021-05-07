<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



class pinax_components_Import extends pinax_components_NullComponent
{
	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		if ($node->hasAttribute('src'))
		{
			$src = $node->getAttribute('src');
			if (strpos($src, '.xml')===strlen($src)-4) {
				$src = substr($src, 0, -4);
			}

			$pageType = pinax_ObjectFactory::resolvePageType($src).'.xml';
	        $path = $compiler->getPath();
	        $fileName = $path.$pageType;

	        if ( !file_exists( $fileName ) )
	        {
	        	$oldFileName = $fileName;
	            $fileName = pinax_findClassPath( $src, true, true);
	            if ( is_null( $fileName ) ) {
	                throw new Exception( 'File non esiste '.$oldFileName );
	            }
	        }

			$compiler2 = pinax_ObjectFactory::createObject('pinax.compilers.Component');
			$compiledFileName = $compiler2->verify($fileName, ['path' => $path]);

			$className = pinax_basename($compiledFileName);
			$componentId = $node->hasAttribute('id') ? $node->getAttribute('id') : '';
			$compiler->_classSource .= '// TAG: '.$node->nodeName.' '.$node->getAttribute('src').PNX_COMPILER_NEWLINE2;
			$compiler->_classSource .= 'if (!$skipImport) {'.PNX_COMPILER_NEWLINE2;
			$compiler->_classSource .= 'pinax_ObjectFactory::requireComponent(\''.$compiledFileName.'\', \''.addslashes($fileName).'\')'.PNX_COMPILER_NEWLINE;
			$compiler->_classSource .= '$n'.$counter.' = new '.$className.'($application, '.$parent.')'.PNX_COMPILER_NEWLINE;
			$compiler->_classSource .= $parent.'->addChild($n'.$counter.')'.PNX_COMPILER_NEWLINE;
			$compiler->_classSource .= '}'.PNX_COMPILER_NEWLINE;
		}
	}
}

