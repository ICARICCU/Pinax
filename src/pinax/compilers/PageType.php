<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_compilers_PageType extends pinax_compilers_Compiler
{
	var $_classSource		= '';
	var $_customClassSource	= '';
	var $_importedPaths = array();
	var $_className			= '';
	var $_path 				= false;
	var $mode 				= null;

	private $modeStack = array();

	function compile($options)
	{
		$this->initOutput();

		if ( isset( $options[ 'pathTemplate' ] ) )
		{
			$fileName = $options[ 'pathTemplate' ].'/pageTypes/'.$options[ 'pageType' ];
			if ( file_exists( $fileName ) )
			{
				$this->_fileName = $fileName;
			}
		}
		if ( isset( $options[ 'mode' ] ) )
		{
			$this->mode = $options[ 'mode' ];
		}

		$pageXml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		if ($this->_fileName) {
			$pageXml->loadAndParseNS( $this->_fileName );
		} else {
			throw new Exception( 'PageType not found '.$options[ 'pageType' ] );
		}
		$pageRootNode 			= $pageXml->documentElement;
		$registredNameSpaces 	= $pageXml->namespaces;
		$registredNameSpaces['pnx'] = 'pinax.components';
		// $idPrefix 				= isset($options['idPrefix']) ? $options['idPrefix'] : '';
		$this->_path			= $options['path'];

		// include i componenti usati
		foreach ($registredNameSpaces as $key=>$value)
		{
			if ($key!='pnx' && substr($value, -1, 1)=='*' && !in_array($value, $this->_importedPaths))
			{
				pinax_loadLocale($value);
				$this->output .= 'pinax_loadLocale(\''.$value.'\')'.PNX_COMPILER_NEWLINE;
				$this->_importedPaths[] = $value;
			}
		}

		$this->_className = pinax_basename($this->_cacheObj->getFileName());
		$this->_classSource .= 'class '.$this->_className.'{'.PNX_COMPILER_NEWLINE2;
		$this->_classSource .= 'function '.$this->_className.'(&$application, $skipImport=false, $idPrefix=\'\') {'.PNX_COMPILER_NEWLINE2;
		$this->_classSource .= '$mode = "'.$this->mode.'"'.PNX_COMPILER_NEWLINE;
		$counter = 0;
		$this->_compileXml($pageRootNode, $registredNameSpaces, $counter, '$application', '');
		$this->_classSource .= '}'.PNX_COMPILER_NEWLINE2;
		$this->_classSource .= '}'.PNX_COMPILER_NEWLINE2;

		$this->output .= $this->_classSource;
		$this->output .= $this->_customClassSource;

		return $this->save();
	}


	function _compileXml(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
		if ($node->nodeType == XML_COMMENT_NODE) return;

		$componentObj = null;
		$componentClassInfo = $this->_getComponentClassInfo($node->nodeName, $registredNameSpaces);
		if (!empty($componentClassInfo['classPath']) && !in_array($componentClassInfo['classPath'], $this->_importedPaths))
		{
			$this->_importedPaths[] = $componentClassInfo['classPath'];
		}
		$compileTranslateMethod = null;
		$compileMethod = null;
		$compileMethodAddPrefix = null;
		if (class_exists($componentClassInfo['className']))
		{
			try {
				$compileTranslateMethod = new ReflectionMethod( $componentClassInfo['className'].'::translateForMode_'.$this->mode );
			    if (!$compileTranslateMethod->isStatic()) $compileTranslateMethod = null;
			} catch (Exception $e) {}
			try {
			    $compileMethod = new ReflectionMethod( $componentClassInfo['className'].'::compile' );
			    if (!$compileMethod->isStatic()) $compileMethod = null;
			} catch (Exception $e) {}
			try {
				$compileMethodAddPrefix = new ReflectionMethod( $componentClassInfo['className'].'::compileAddPrefix' );
				if (!$compileMethodAddPrefix->isStatic()) $compileMethodAddPrefix = null;
			} catch (Exception $e) {}
		}

		if ($compileTranslateMethod) {
			$newNodeXml = $compileTranslateMethod->invoke(null, $node);
			if ($newNodeXml) {
				$partXml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
				$partXml->loadXmlAndParseNS( $newNodeXml , LIBXML_NOERROR );
				$newNode = $partXml->documentElement;
				$this->addNamespace($partXml->namespaces, $registredNameSpaces);
				$this->_compileXml($newNode, $registredNameSpaces, $counter, $parent, $idPrefix, $idPrefixOriginal);
				$oldcounter = $counter;

				if (strpos($newNodeXml, 'noChild="true"')===false) {
					$this->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix );
				}
			}
			return;
		}

		// sostituisce i caratteri speciali all'interno del nome del tag
		// per poter verificare se ??? stato deifnito un metodo aaposito
		// per compilare il tag
		$methodName = 'compile_'.preg_replace('/[\-\#\:]/', '', $node->nodeName);
		if (method_exists($this, $methodName))
		{
			$this->$methodName($node, $registredNameSpaces, $counter, $parent, $idPrefix, $idPrefixOriginal);
			return;
		}
		else
		{
			$this->_classSource .= '// TAG: '.$node->nodeName.PNX_COMPILER_NEWLINE2;
			$componentId = $node->hasAttribute( 'id' ) ? $node->getAttribute( 'id' ) : 'c'.md5(uniqid(rand(), true));
			$componentIdPrefix = $idPrefix;
			$compileOnlyChild = false;

			if ($compileMethod)	{
				$this->pushEditMode($node);
				$r = $compileMethod->invokeArgs(null, array($this, &$node, &$registredNameSpaces, &$counter, $parent, $idPrefix, $componentClassInfo, $componentId));
				$this->popEditMode();
				if ($r!==true) {
					// non ha figli
					return true;
				}
				$compileOnlyChild = true;
			}
			if (!$compileOnlyChild) {
				if ($compileMethodAddPrefix) {
					$componentIdPrefix = $compileMethodAddPrefix->invokeArgs(null, array($this, &$node, $componentId, $idPrefix));
				}

				$this->_classSource .= '$n'.$counter.' = &pinax_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', $idPrefix.'.$idPrefix.'\''.$componentId.'\', '.$idPrefixOriginal.'\''.$componentId.'\', $skipImport, $mode)'.PNX_COMPILER_NEWLINE;

				if ($parent!='NULL')
				{
					$this->_classSource .= $parent.'->addChild($n'.$counter.')'.PNX_COMPILER_NEWLINE;
				}

				if (count($node->attributes))
				{
					// compila  gli attributi
					$this->_classSource .= '$attributes = array(';
					foreach ( $node->attributes as $index=>$attr )
					{
						if ($attr->name!='id')
						{
							// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
							$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
							$this->_classSource .= '\''.$prefix.$attr->name.'\' => \''.str_replace('\'', '\\\'', $attr->value).'\', ';
						}
					}

					$this->_classSource .= ')'.PNX_COMPILER_NEWLINE;
					$this->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.PNX_COMPILER_NEWLINE;
				}
			}
			$idPrefix = $componentIdPrefix;
			$oldcounter = $counter;
			$this->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix );
		}
	}

	private function pushEditMode($node)
	{
		$this->modeStack[] = $this->mode;
		$this->mode = $node->hasAttribute('pnx:editMode') ? $node->getAttribute('pnx:editMode') : $this->mode;
	}

	private function popEditMode()
	{
		$this->mode = array_pop($this->modeStack);
	}


	function compileChildren(&$node, &$registredNameSpaces, &$counter, $oldcounter='NULL', $idPrefix='') {
		$this->pushEditMode($node);

		foreach( $node->childNodes as $nc )
		{
			$counter++;
			$this->_compileXml($nc, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
		}

		if ( $node->hasAttribute( 'allowModulesSnippets' )  && $node->getAttribute( 'allowModulesSnippets' ) == "true")
		{
			$modulesState = pinax_Modules::getModulesState();
			$modules = pinax_Modules::getModules();

			foreach( $modules as $m )
			{
				$isEnabled = !isset( $modulesState[ $m->id ] ) || $modulesState[ $m->id ];
				if ( $isEnabled && $m->pluginInPageType && $m->pluginSnippet )
				{
					$counter++;
					$this->compile_pnxinclude( $m->pluginSnippet, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix );
				}
			}
		}

		$this->popEditMode();
	}

	function compileChildNode(&$node, &$registredNameSpaces, &$counter, $oldcounter='NULL', $idPrefix='')
	{
		$this->_compileXml($node, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
	}

	function _getComponentClassInfo($componentName, &$registredNameSpaces)
	{
		$result = array('className' => '', 'classPath' => '');
		$componentClassName = explode(':', $componentName);
		if (count($componentClassName)==2)
		{
			$nameSpace 			= $componentClassName[0];
			$componentClassName = $componentClassName[1];
			if (array_key_exists($nameSpace, $registredNameSpaces))
			{
				if ($registredNameSpaces[$nameSpace]!='*')
				{
					if (strpos($registredNameSpaces[$nameSpace], '\\')===false) {
						$componentClassName = rtrim($registredNameSpaces[$nameSpace], '.*').'.'.$componentClassName;
					} else {
						$componentClassName = rtrim($registredNameSpaces[$nameSpace], '\\*').'\\'.$componentClassName;
					}
				}
				$result['classPath'] = $componentClassName;
				$componentClassName = str_replace(['*', '.'], ['', '_'], $componentClassName);
				$result['className'] = $componentClassName;
			}
			else
			{
				// TODO
				// namespace non definito
				// visualizzare un errore

			}
		}
		else
		{
			$result['className'] = $componentName;
		}
		return $result;
	}

	function compile_cdatasection(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
		$this->compile_text($node, $registredNameSpaces, $counter, $parent);
	}

	function compile_text(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
		$this->_classSource .= '$tagContent = <<<EOD'.PNX_COMPILER_NEWLINE2;
		$this->_classSource .=  str_replace('$', '\$', $node->nodeValue).PNX_COMPILER_NEWLINE2;
		$this->_classSource .= 'EOD'.PNX_COMPILER_NEWLINE;
		$this->_classSource .= $parent.'->setContent($tagContent)'.PNX_COMPILER_NEWLINE;
	}


	function compile_pnxif(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
		$condition = $node->getAttribute( "condition" );
		$condition = str_replace( array( '$application.', '$user.' ), array( '$application->', '$application->getCurrentUser()->' ), $condition );
		$this->_classSource .= 'if ('.$condition.') {'.PNX_COMPILER_NEWLINE2;
		foreach( $node->childNodes as $nc )
		{
			$this->_compileXml($nc, $registredNameSpaces, $counter, $parent, $idPrefix, $idPrefixOriginal);
		}
		$this->_classSource .= '} // end if'.PNX_COMPILER_NEWLINE2;
	}


	function compile_pnxtemplateDefine(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
	}

	function compile_pnxinclude(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
		$origSrc = is_object( $node ) ? $node->getAttribute( 'src' ) : $node;
		$tempSrc = $origSrc;
		$src = null;
		if ( strpos( $tempSrc, '.xml' ) === false ) {
			$tempSrc .= '.xml';
		}

		if ($node->hasAttribute('override') && $node->getAttribute('override') === 'true') {
			$src = __Paths::getRealPath('APPLICATION_PAGE_TYPE', $tempSrc);
		}

		$tempSrc = dirname( $this->_fileName ).'/'.$tempSrc;
		if (!$src && file_exists($tempSrc)) {
			$src = $tempSrc;
		}

		if (!$src) {
			$src = pinax_findClassPath( $origSrc, true, true);
		}

		if (!$src) {
			throw pinax_compilers_PageTypeException::templateDefinitionDontExixts($origSrc);
		}

		$this->_classSource .= '// include: '.$src.PNX_COMPILER_NEWLINE2;
		$srcXml = file_get_contents($src);

		// esegue il parsing per sapere quale sono i parametri del template e ricavare quelli di default
		$templateParams = array();
		$includeXML = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		$includeXML->loadXmlAndParseNS( $srcXml );
		$templateDefineNodes = $includeXML->getElementsByTagName('templateDefine');
		foreach ($templateDefineNodes as $templateDefine) {
			if (!$templateDefine->hasAttribute('name')) {
				throw pinax_compilers_PageTypeException::templateDefineNotValid($src);
			}
			$templateParams[$templateDefine->getAttribute('name')] = $templateDefine->hasAttribute('required') && $templateDefine->getAttribute('required')=='true' ? null :
																				( $templateDefine->hasAttribute('defaultValue') ? $templateDefine->getAttribute('defaultValue') : '');

		}

		if (count($templateParams)) {
			$templateParamsKeys = array_keys($templateParams);
			foreach( $node->childNodes as $nc ) {
				if ($nc->tagName=='pnx:template' && $nc->hasAttribute('name')) {
					$name = $nc->getAttribute('name');
					if (!in_array($name, $templateParamsKeys)) {
						throw pinax_compilers_PageTypeException::templateDefinitionDontExixts($name);
					}

					$value = '';
					if ($nc->hasAttribute('value')) {
						$value = $nc->getAttribute('value');
					} else {
						$tempDom = new DOMDocument();
						foreach( $nc->childNodes as $ncc ) {
	     					$tempDom->appendChild($tempDom->importNode($ncc,true));
	     				}
	     				$value = $tempDom->saveXML();
	     				$value = str_replace('<?xml version="1.0"?>', '', $value);
					}

					$templateParams[$name] = $value;
				}
			}

			foreach($templateParams as $k=>$v) {
				if (is_null($v)) {
					throw pinax_compilers_PageTypeException::templateDefinitionRequired($k, $src);
				}
				$srcXml = str_replace( '##'.$k.'##', $v, $srcXml );
			}
		}

		$includeXML = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		$includeXML->loadXmlAndParseNS( $srcXml );
		$newNameSpaces = $includeXML->namespaces;
		$this->addNamespace($newNameSpaces, $registredNameSpaces);
		if ( $includeXML->documentElement->hasAttribute( 'adm:editComponents' ) )
		{
			$this->_classSource .= 'if ($n0) $n0->setAttribute( "adm:editComponents", "'.$includeXML->documentElement->getAttribute( 'adm:editComponents' ).'" )'.PNX_COMPILER_NEWLINE;
		}

		if ($includeXML->documentElement->nodeName=='pnx:include') {
			foreach($includeXML->documentElement->childNodes as $nc ) {
				$counter++;
				$this->_compileXml($nc, $registredNameSpaces, $counter, $parent, $idPrefix, $idPrefixOriginal);
			}
		} else {
			$this->_compileXml($includeXML->documentElement, $registredNameSpaces, $counter, $parent, $idPrefix, $idPrefixOriginal);
		}
	}

	function compile_baseTag(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		$this->_classSource .= '$n'.$counter.' = &pinax_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', $idPrefix.'.$idPrefix.'\''.$componentId.'\', \''.$componentId.'\', $skipImport)'.PNX_COMPILER_NEWLINE;

		if ($parent!='NULL')
		{
			$this->_classSource .= $parent.'->addChild($n'.$counter.')'.PNX_COMPILER_NEWLINE;
		}

		if (count($node->attributes))
		{
			// compila  gli attributi
			$this->_classSource .= '$attributes = array(';
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->name!='id')
				{
					// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
					$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
					$this->_classSource .= '\''.$prefix.$attr->name.'\' => \''.addslashes($attr->value).'\', ';
				}
			}

			$this->_classSource .= ')'.PNX_COMPILER_NEWLINE;
			$this->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.PNX_COMPILER_NEWLINE;
		}
	}

	function getPath()
	{
		return $this->_path;
	}

	protected function addNamespace($newNameSpaces, &$registredNameSpaces)
	{
		foreach ($newNameSpaces as $key=>$value)
		{
			if ( isset( $registredNameSpaces[ $key ] ) ) continue;

			if ($key!='pnx' && substr($value, -1, 1)=='*' && !in_array($value, $this->_importedPaths))
			{
				$this->_importedPaths[] = $value;
			}
			$registredNameSpaces[ $key ] = $value;
		}
	}
}
