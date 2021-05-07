<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_compilers_Routing extends pinax_compilers_Compiler
{
	private $language;
	private $prog = 0;

    /**
     *
     */
	function compile($options)
	{
		$this->addEventListener(PNX_EVT_LISTENER_COMPILE_ROUTING, $this);
		$this->initOutput();

		if ( __Config::get( 'MULTILANGUAGE_ENABLED' ) )
		{
			$this->language = '{language}/';
		}

        $evt = array('type' => PNX_EVT_START_COMPILE_ROUTING);
        $this->dispatchEvent($evt);

		if ( strpos( $this->_fileName, 'routing.xml') !== false )
		{
			$modules = pinax_Modules::getModules();
			foreach( $modules as $m )
			{
				$path = $m->path ?  (is_array($m->path) ? $m->path['path'] : $m->path) : pinax_findClassPath( $m->classPath );

                if (!is_array($m->path) && !file_exists( $path . '/config/routing.xml' )) {
                    // i moduli non PSR4 installati con composer
                    // hanno il path per risolvere il classpath quindi falliscono con il file_exixts
                    $path = pinax_findClassPath( $m->classPath );
                }

				if ( !is_null( $path ) && file_exists( $path . '/config/routing.xml' ) )
				{
					$this->compileFile( $path . '/config/routing.xml' );
				}
			}
		}

		$this->compileFile( $this->_fileName );
		return $this->save();
	}

    /**
     * @param $fileName
     */
	function compileFile( $fileName )
	{
        /** @var pinax_parser_XML $xml */
		$xml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		$xml->loadAndParseNS( $fileName );
		$this->compileXml($xml);
	}

    /**
     * @param $xmlString
     */
	function compileString($xmlString)
	{
		$xml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		$xml->loadXmlAndParseNS($xmlString);
		$this->compileXml($xml);
	}

    /**
     * @param pinax_parser_XML $xml
     */
	private function compileXml($xml, $prefix='', $middleware='')
	{
		if ($xml->hasChildNodes()) {

			foreach ($xml->childNodes as $node) {
				if ( $node->nodeName == "pnx:Routing" ) {
					$this->compileXml($node, $prefix, $middleware);
				} else if ( $node->nodeName == "pnx:Route" ) {
					$this->compileRouteNode($node, $prefix, $middleware);
				} else if ( $node->nodeName == "pnx:RouteGroup" ) {
					if (!$node->hasAttribute('value') || $node->getAttribute('value')=='') {
						throw new Exception('pnx:RouteGroup need value attribute');
					}
					$this->compileXml($node, $prefix.$node->getAttribute('value'), $middleware);
				} else if ( $node->nodeName == "pnx:Middleware" ) {
					if (!$node->hasAttribute('class') || $node->getAttribute('class')=='') {
						throw new Exception('pnx:Middleware need class attribute');
					}
					$this->compileXml($node, $prefix, $node->getAttribute('class'));
				}
			}
		}
	}

    /**
     * @param DOMElement $xml
     * @param String $prefix
     * @param String $middleware
     */
	private function compileRouteNode($param, $prefix='', $middleware='')
	{
		$this->prog++;
		$name 	= strtolower( $param->getAttribute('name') );
		if ( empty( $name ) )
		{
			$name = (string)$this->prog;
			$name = $param->hasAttribute('method') ? strtolower( $param->getAttribute('method') ).'_'.$name : 'internal_'.$name;
		}

		// controlla se il nodo ha dei figli
		if ( $param->hasChildNodes() )
		{
			$this->output .= '$configArray["'.$name.'"] = array()'.PNX_COMPILER_NEWLINE;

			foreach( $param->childNodes as $node )
			{
				if ( $node->nodeName == "pnx:RouteCondition" )
				{
					$this->compileNode($node, $name, true, $prefix, $middleware);
				}
			}
		}
		else
		{
			$this->compileNode($param, $name, false, $prefix, $middleware);
		}
	}

    /**
     * @param DOMElement $param
     * @param      $name
     * @param bool $child
     */
	private function compileNode( &$param, $name, $child, $prefix, $middleware, $pageNode=null )
	{
		$value 	= $param->hasAttribute('value') ? $param->getAttribute('value') : $param->firstChild->nodeValue;
		$value = !$param->hasAttribute('method') && !$param->hasAttribute('skipLanguage') ? $this->language.$prefix.$value : $prefix.$value;
		$parseUrl = $param->hasAttribute('parseUrl') ? $param->getAttribute('parseUrl') : 'true';
		$keyName = $param->hasAttribute('keyName') ? $param->getAttribute('keyName') : '';
		$keyValue = $param->hasAttribute('keyValue') ? $param->getAttribute('keyValue') : '';
		$method = $param->hasAttribute('method') ? strtolower( $param->getAttribute('method') ) : '';
		$enabled = $param->hasAttribute('enabled') ?  $param->getAttribute('enabled') : '';
		$child = $child ? '[]' : '';

		$urlPattern = '';
		$urlValues = array();
		$staticValues = array();
		if ($middleware) {
			$staticValues['__middleware__'] = $middleware;
		}

		/** @var pinax_application_Application $application */
		$application = &pinax_ObjectValues::get('org.pinax', 'application');
		$siteMap = &$application->getSiteMap();
		$isApplicationDB = $siteMap && $siteMap->getType() == 'db';

		if ( $parseUrl == 'true' )
		{
			$attributeToSkip = array( 'name', 'value', 'parseUrl', 'keyName', 'keyValue', 'enabled' );
			foreach ( $param->attributes as $index=>$attr )
			{
				// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
				$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
				$attrName = $prefix.$attr->name;
				if ( !in_array( $attrName, $attributeToSkip ) )
				{
					$staticValues[ $attrName ] = $attr->value;
				}
			}

			$urlPattern = str_replace('/', '\/', $value );
			preg_match_all("|\{(.*)\}|U", $urlPattern, $match, PREG_PATTERN_ORDER);
			for($i=0; $i<count($match[0]); $i++)
			{
				$command = explode('=', $match[1][$i]);
				$urlValuesKey = $command[count($command)-1];
				switch ($command[0])
				{
					case 'language':
						$urlPartValue = '(.{2})';
						break;
					case '*':
					case 'currentMenu':
						$urlPartValue = '(.*)';
						$urlValuesKey = 'pageId';
						break;
					case 'pageId':
						if (count($command)>1 && is_object( $siteMap ) ) {
							if ($pageNode) {
								$page = $pageNode;
							} else {
								$page = null;
								if (is_numeric($command[1])) {
									$page = $siteMap->getNodeById($command[1]);
								} else {
									$pages = $this->menuFromPageType($siteMap, $command[1]);
									if (count($pages)>1) {
										$this->compileMutiplePageTypeNode($param, $name, $child, $prefix, $middleware, $pages);
										return;
									} else if (count($pages)==1) {
										$page = $pages[0];
									}
								}
							}
							if ( is_null( $page ) ) {
	                             $urlPartValue = 'not:available:'.$command[1];
	                        } else {
								if ( $isApplicationDB ) {
                                    if ($page->url) {
										$pageUrl = preg_replace('/^(.{2}\/)/', '', $page->url);
										$pageUrl = str_replace('/', '\/', $pageUrl);
                                        $urlPartValue =  strtolower('('.$pageUrl.'[^\/]*?)');
                                        $staticValues[ 'pageId' ] = $page->id;
                                    } else {
                                        $urlPartValue =  strtolower('('.$page->id.'\/[^\/]*?)');
                                    }
								} else {
									$urlPartValue =  strtolower('('.str_replace('/', '\/', $page->id).')');
								}
							}
						} else {
							$urlPartValue = '([^\/]*)';
						}
						$urlValuesKey = $command[0];
						break;
					case 'pageTitle':
						$urlPartValue = '([^\/]*)';
						break;
					case 'i18n':
						$urlPartValue = '('.str_replace('/', '\/', strtolower(__T($command[1]))).')';
						break;
					case 'value':
					case 'valueRaw':
					case 'valuePlain':
						$urlPartValue = '([^\/?]*)';
						break;
					case 'integer':
						$urlPartValue = '(\d*)';
						break;
					case 'static':
						$urlPartValue = '';
						$urlValuesKey = $command[1];
						break;
					case 'state':
						$urlPartValue = '('.$command[1].')';
						$urlValuesKey = $command[0];
						break;
					case 'config':
						$urlPartValue = '';
						$urlValuesKey = '';
						break;
					default:
						$urlPartValue = '('.(count($command) > 1 ? $command[1] : $command[0]).')';
						$urlValuesKey = $command[0];
						break;
				}


				if (empty($urlPartValue))
				{
					$urlPattern = str_replace(array( $match[0][$i].'\/', $match[0][$i] ) , '()', $urlPattern);
					$urlValues[$urlValuesKey] = $command[2];
					continue;
				}
				$urlValues[$urlValuesKey] = '';
				$urlPattern = str_replace($match[0][$i], $urlPartValue, $urlPattern);
				if (strpos($urlPattern, '#')!==false) {
					list($urlPattern) = explode('#', $urlPattern);
				}
			}

			$urlPattern = rtrim( $urlPattern, '\/' );
			$urlPattern = '|^'.$urlPattern.'(\/?)$|i';
		}

		if (isset($staticValues['pageId']) && $isApplicationDB && !is_numeric($staticValues['pageId'])) {
			$page = null;
			if ($pageNode) {
				$page = $pageNode;
			} else {
				$pages = $this->menuFromPageType($siteMap, $staticValues['pageId']);
				if (count($pages)>1) {
					$this->compileMutiplePageTypeNode($param, $name, $child, $prefix, $middleware, $pages);
					return;
				} else if (count($pages)==1) {
					$page = $pages[0];
				}
			}

			if ($page) {
				$staticValues['pageId'] = $page->id;
			}
		}

		$this->output .= '$configArray["'.$name.'"]'.$child.' = array("value" => "'.addslashes($value).'", "urlPattern" => "'.addcslashes($urlPattern, '"\\' ).'", "urlValues" => "'.addcslashes( serialize( $urlValues ), '"\\' ).'", "staticValues" => "'.addcslashes( serialize( $staticValues ), '"\\' ).'", "parseUrl" => '.$parseUrl.', "keyName" => "'.$keyName.'", "keyValue" => "'.$keyValue.'", "method" => "'.$method.'", "enabled" => "'.$enabled.'" )'.PNX_COMPILER_NEWLINE;
	}

	private function compileMutiplePageTypeNode(&$param, $name, $child, $prefix, $middleware, $pageNodes)
	{
		$i = 0;
		foreach ($pageNodes as $pageNode) {
			$suffix = $i > 0 ? '#'.$pageNode->id : '';
			$this->compileNode($param, $name.$suffix, $child, $prefix, $middleware, $pageNode);
			$i++;
		}
	}


    /**
     * @param $evt
     */
	public function listenerCompileRouting($evt)
	{
		$xmlString = $evt->data;
		$this->compileString($xmlString);
	}


	private function menuFromPageType($siteMap, $pageType)
	{
		$pages = $siteMap->getMenusByPageType($pageType);
		if (!count($pages)) {
			$module = pinax_Modules::getModule($pageType);
			if (!is_null($module) && $module->pageType) {
				$pages = $siteMap->getMenusByPageType($module->pageType);
			}
		}
		return $pages;
	}
}
