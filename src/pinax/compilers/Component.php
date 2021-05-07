<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_compilers_Component extends pinax_compilers_PageType
{

    /**
     * @param $options
     *
     * @return mixed
     */
	function compile($options)
	{
		$this->initOutput();
		if ( isset( $options[ 'mode' ] ) )
		{
			$this->mode = $options[ 'mode' ];
		}

		$this->_path = isset($options[ 'path']) ? $options['path'] : '';

		$pageXml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		$pageXml->loadAndParseNS( $this->_fileName );
		$pageRootNode 			= $pageXml->documentElement;
		$registredNameSpaces 	= $pageXml->namespaces;
		$registredNameSpaces['pnx'] = 'pinax.components';

		// include i componenti usati
		foreach ($registredNameSpaces as $key=>$value)
		{
			if ($key!='pnx' && substr($value, -1, 1)=='*')
			{
				$this->output .= 'pinax_loadLocale(\''.$value.'\')'.PNX_COMPILER_NEWLINE;
			}
		}

		$className = pinax_basename($this->_cacheObj->getFileName());
		$componentClassInfo = $this->_getComponentClassInfo($pageRootNode->nodeName, $registredNameSpaces);

		if (class_exists($componentClassInfo['className']))
		{
			$compileTranslateMethod = null;
			try {
				$compileTranslateMethod = new ReflectionMethod( $componentClassInfo['className'].'::translateForMode_'.$this->mode );
			    if (!$compileTranslateMethod->isStatic()) $compileTranslateMethod = null;
			} catch (Exception $e) {}
			if ($compileTranslateMethod) {
				$newNodeXml = $compileTranslateMethod->invoke(null, $pageRootNode);
				if ($newNodeXml) {
					$partXml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
					$partXml->loadXmlAndParseNS( $newNodeXml , LIBXML_NOERROR );
					$newNode = $partXml->documentElement;
					$this->addNamespace($partXml->namespaces, $registredNameSpaces);
					$componentClassInfo = $this->_getComponentClassInfo($newNode->nodeName, $registredNameSpaces);
				}
			}
		}

		$this->_classSource .= 'class '.$className.' extends '.$componentClassInfo['className'].' {'.PNX_COMPILER_NEWLINE2;
		$this->_classSource .= 'function '.$className.'(&$application, &$parent, $tagName=\'\', $id=\'\', $originalId=\'\', $skipImport=false) {'.PNX_COMPILER_NEWLINE2;
		if (isset($options['originalClassName'])) $this->_classSource .= '$this->_className = \''.$options['originalClassName'].'\''.PNX_COMPILER_NEWLINE;
		$this->_classSource .= 'parent::__construct($application, $parent, $tagName, $id, $originalId)'.PNX_COMPILER_NEWLINE;
		$this->_classSource .= '$mode = ""'.PNX_COMPILER_NEWLINE;
		$this->_classSource .= '$idPrefix = ""'.PNX_COMPILER_NEWLINE;
		$this->_classSource .= '$n0 = &$this'.PNX_COMPILER_NEWLINE;
		$this->_classSource .= 'if (!empty($id)) $id .= \'-\''.PNX_COMPILER_NEWLINE;
		$this->_classSource .= 'if (!empty($originalId)) $originalId .= \'-\''.PNX_COMPILER_NEWLINE;

		if (count($pageRootNode->attributes))
		{
			// compila  gli attributi
			$this->_classSource .= '$attributes = array(';
			foreach ( $pageRootNode->attributes as $index=>$attr )
			{
				if ($attr->name!='id')
				{
					// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
					$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
					$this->_classSource .= '\''.$prefix.$attr->name.'\' => \''.addslashes($attr->value).'\', ';
				}
			}
			$this->_classSource .= ')'.PNX_COMPILER_NEWLINE;
			$this->_classSource .= '$this->setAttributes( $attributes )'.PNX_COMPILER_NEWLINE;
		}

		$counter = 0;
		$oldcounter = $counter;
		foreach( $pageRootNode->childNodes as $nc )
		{
			$counter++;
			$this->_compileXml($nc, $registredNameSpaces, $counter, '$n'.$oldcounter, '$id.', '$originalId.' );
		}

		if (isset($options['originalClassName']) && $pageRootNode->hasAttribute( 'allowModulesSnippets' )  && $pageRootNode->getAttribute( 'allowModulesSnippets' ) == "true" )
		{
			$modulesState = pinax_Modules::getModulesState();
			$modules = pinax_Modules::getModules();

			foreach( $modules as $m )
			{
				$isEnabled = !isset( $modulesState[ $m->id ] ) || $modulesState[ $m->id ];
				if ( $isEnabled && $m->pluginInPageType && $m->pluginSnippet )
				{
					$counter++;
					$this->compile_pnxinclude( $m->pluginSnippet, $registredNameSpaces, $counter, '$n'.$oldcounter, '$id.' );
				}
			}
		}
		$this->_classSource .= '}'.PNX_COMPILER_NEWLINE2;
		$this->_classSource .= '}'.PNX_COMPILER_NEWLINE2;

		$this->output .= $this->_classSource;
		$this->output .= $this->_customClassSource;

		return $this->save();
	}
}
