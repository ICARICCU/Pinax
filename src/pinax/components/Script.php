<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



class pinax_components_Script extends pinax_components_NullComponent
{


	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		if ( $node->hasAttribute( 'extendParent' ) )
		{
			$scriptClassName = $compiler->_className.'__class__'.$counter;
			preg_match('/(\\'.$parent.'\s*=\s*&\s*pinax_ObjectFactory::createComponent\(\')([^\']*)(.*)/u', $compiler->_classSource, $matches, PREG_OFFSET_CAPTURE);
			$originalClassName = str_replace('.', '_', $matches[2][0]);
			$compiler->_customClassSource .= 'class '.$scriptClassName.' extends '.$originalClassName.PNX_COMPILER_NEWLINE2;
			$compiler->_customClassSource .= '{'.$node->nodeValue.PNX_COMPILER_NEWLINE2.'}'.PNX_COMPILER_NEWLINE2;
			$compiler->_classSource = preg_replace('/(\\'.$parent.'\s*=\s*&\s*pinax_ObjectFactory::createComponent\(\')([^\']*)/', '$1'.$scriptClassName, $compiler->_classSource);
		}
		else if ( $node->hasAttribute( 'target' ) )
		{
			$scriptClassName = $compiler->_className.'__class__'.$counter;
			$compiler->_customClassSource .= 'class '.$scriptClassName.PNX_COMPILER_NEWLINE2;
			$compiler->_customClassSource .= '{'.$node->nodeValue.PNX_COMPILER_NEWLINE2.'}'.PNX_COMPILER_NEWLINE2;
			$compiler->_classSource .= '$'.$scriptClassName.' = &'.$parent.'->getComponentById(\''.$node->attributes['target'].'\')'.PNX_COMPILER_NEWLINE;
			$compiler->_classSource .= '$'.$scriptClassName.'->setCustomClass(\''.$scriptClassName.'\')'.PNX_COMPILER_NEWLINE;
		}
		else
		{
			$compiler->output .= $node->nodeValue.PNX_COMPILER_NEWLINE;
		}
	}
}
