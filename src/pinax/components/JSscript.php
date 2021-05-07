<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_JSscript extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('src', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('folder', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('type', false, 'text/javascript', COMPONENT_TYPE_STRING);
		$this->defineAttribute('inline', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('onlyLocale', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('editableRegion', 	false, 	'head', COMPONENT_TYPE_STRING);
		$this->defineAttribute('extension', false, 'js', COMPONENT_TYPE_STRING);
		$this->defineAttribute('minify', false, !__Config::get('DEBUG'), COMPONENT_TYPE_BOOLEAN);

		parent::init();
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		$folder = $this->getAttribute('folder');
		$src = $this->getAttribute('src');
		$type = $this->getAttribute('type');

        $language = $this->_application->getLanguage();
        $language2 = $language.'-'.strtoupper($language);
        $src = str_replace(array('##LANG##', '##LANG2##'), array($language, $language2), $src);
		$folder = str_replace(array('##LANG##', '##LANG2##'), array($language, $language2), $folder);

		if ( $folder )
		{
			if (!pinax_ObjectValues::get('pinax.JS', 'run', false))
			{
				pinax_ObjectValues::set('pinax.JS', 'run', true);
				$pageType = $this->_application->getPageType();
				$state = __Request::get( 'action', '' );
				$params = [];
				if (__Request::exists('id')) {
					$params['id'] = __Request::get('id');
				}
				// $params = __Request::getAllAsArray();
				// unset($params['__params__']);
				// unset($params['__routingName__']);
				// unset($params['__routingPattern__']);
				// unset($params['__url__']);
				// unset($params['__back__url__']);
				// unset($params['PHP_AUTH_USER']);
				// unset($params['PHP_AUTH_PW']);
				$params = json_encode($params);
				$jsCode = <<<EOD
var PinaxApp = {};
PinaxApp.pages = {};
jQuery( function(){
	if ( typeof( PinaxApp.pages[ '$pageType' ] ) != 'undefined' )
	{
		PinaxApp.pages[ '$pageType' ]( '$state', $params );
	}
})
EOD;

				$this->addOutputCode( pinax_helpers_JS::JScode( $jsCode ), 'head' );
			}

			// include tutta una cartella
			$jsFileName = $this->includeFolder( $folder, $language);
			if ( $this->getAttribute('inline')) {
				$js = file_get_contents($jsFileName);
				if ( strpos($js, '<script') !== false ) {
					$this->addOutputCode( $js );
				} else {
					$this->addOutputCode( pinax_helpers_JS::JScode( $js, $type ) );
				}
			} else {
				$minify = $this->getAttribute('minify');
				$this->addOutputCode( pinax_helpers_JS::linkJSfile( $jsFileName.(!$minify ? '?'.microtime(true) : '') , null, $type) );
			}
		}
		else
		{
			if ($src)
			{
				$this->addOutputCode( pinax_helpers_JS::linkJSfile( $src, null, $type ) );
			}
			else
			{
				$this->addOutputCode( pinax_helpers_JS::JScode( $this->replaceLocale($this->getText()), $type ) );
			}
		}
	}

	private function includeFolder( $folder, $language )
	{
		// controlla se il file in cache è valido
		$options = array(
			'cacheDir' => pinax_Paths::get('CACHE_JS'),
			'lifeTime' => __Config::get('CACHE_CODE'),
			'hashedDirectoryLevel' => __Config::get('CACHE_CODE_DIR_LEVEL'),
			'readControlType' => '',
			'fileExtension' => '.js'
		);

		$cacheSignature = get_class($this).$folder.$language.__Config::get('APP_VERSION');
		$cacheObj = pinax_ObjectFactory::createObject( 'pinax.cache.CacheFile', $options );
		$jsFileName = $cacheObj->verify( $cacheSignature );
		if ($jsFileName===false)
		{
			$jsFile = '';
			$folder = pinax_findClassPath($folder);
			$extension = $this->getAttribute('extension');
			$onlyLocale = $this->getAttribute('onlyLocale');
	        $language = $this->_application->getLanguage();
	        $language2 = $language.'-'.strtoupper($language);


			foreach(glob($folder.'/*'.$extension) as $file) {
				if ($onlyLocale && !in_array(pathinfo($file, PATHINFO_FILENAME), [$language, $language2])) {
					continue;
				}
	            $jsCode = file_get_contents($file);
				$jsCode = $this->replaceLocale($jsCode);
				$jsFile .= $jsCode."\n";
	        }

			// NOTE: necesssario per la macchina vagrant
            // per problemi di sincronizzazione del file
            @unlink($cacheObj->getFileName());

	        $minify = $this->getAttribute('minify');
			if ( !$minify || $this->getAttribute('inline'))
			{
				$cacheObj->save( $jsFile, NULL, get_class($this) );
			}
			else
			{
				$cacheObj->save( JSMin::minify( $jsFile ), NULL, $cacheSignature );
			}
			$jsFileName = $cacheObj->getFileName();
		}
		return $jsFileName;
	}

	private function replaceLocale($text)
	{
	    preg_match_all('/(\{)((i18n:)([^(\'"\})]*))(\})/', $text, $matches, PREG_OFFSET_CAPTURE);
	    if (count($matches[0])) {
	        for ($i=count($matches[0])-1; $i>=0;$i--) {
	            $text = str_replace($matches[0][$i][0], __Tp($matches[4][$i][0]), $text);
	        }
	    }

	    preg_match_all('/(\{)((config:)([^(\}]*))(\})/', $text, $matches, PREG_OFFSET_CAPTURE);
	    if (count($matches[0])) {
	        for ($i=count($matches[0])-1; $i>=0;$i--) {
	            $text = str_replace($matches[0][$i][0], __Config::get($matches[4][$i][0]), $text);
	        }
	    }
	    return $text;
	}
}
