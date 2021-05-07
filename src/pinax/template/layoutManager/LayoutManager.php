<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_template_layoutManager_LayoutManager extends PinaxObject
{
	protected $fileName;
	protected $rootPath;
	protected $language = '';
	protected $currentMenu = '';
	protected $replacePath;

	function __construct($fileName='', $replacePath='')
	{
		$this->fileName = pinax_Paths::getRealPath('APPLICATION_TEMPLATE', $fileName);
		$this->rootPath = pinax_Paths::get('APPLICATION_TEMPLATE');
		$this->replacePath = $replacePath ? : pinax_Paths::get('APPLICATION_TEMPLATE');

		$application = &pinax_ObjectValues::get('org.pinax', 'application');
		$this->currentMenu = $application->getCurrentMenu();
		$this->language = $application->getLanguage();

		if ( !file_exists( $this->fileName ) )
		{
			pinax_Exception::show( 500, "Template non trovato: ".$this->rootPath.$fileName, "", "");
			exit;
		}
	}

	function apply(&$regionContent)
	{
		return $regionContent;
	}

	function modifyBodyTag($value, $templateSource)
	{
		return str_replace('<body', '<body '.$value, $templateSource);
	}

	function checkRequiredValues( &$regionContent )
	{
		if (!isset($regionContent['docTitle']))
		{
			$regionContent['docTitle'] = $this->currentMenu->title.' - '.__Config::get('APP_NAME');
		}
		// compatibility fix
		$regionContent['doctitle'] = $regionContent['docTitle'];
	}

	function fixUrl($templateSource)
	{
		$templateSource = preg_replace("/<(.*?)(href|src|background)\s*=\s*(\'|\")(?!((http|https|ftp|mailto|javascript|data):|#|<\?php|\/\/))(.*?)(\'|\")(.*?)>/si", "<$1$2=$3" . $this->replacePath . "$6$7$8>", $templateSource);

		$templateSource = preg_replace("/(\s+url\s*?\([\'\"]*)(?!((http|https|data):))(.*?)([\'\"]*\))/i", "$1" . $this->replacePath . "$4$5", $templateSource);
		return $templateSource;
	}

	function fixLanguages( $templateSource )
	{
		$templateSource = preg_replace('/(<head>|<head(\s[^>]*)>)/', '<head$2><base href="'.PNX_HOST.'/" />', $templateSource, 1);
		$templateSource = preg_replace('/(\<html.*xml:lang=)"([^"]*)"/', '$1"'.$this->language.'"', $templateSource );
		$templateSource = preg_replace('/(\<html.*lang=)"([^"]*)"/', '$1"'.$this->language.'"', $templateSource );

		if ( __Config::get('SEF_URL') === 'full' )
		{
			// non è il massimo ma la regexp su testi lunghi crasha
			$templateSource = str_replace( array( 'href="#', 'href=\'#', 'href="noTranslate:' ), array( 'href="'.__Routing::scriptUrl().'#', 'href=\''.__Routing::scriptUrl().'#', 'href="'), $templateSource );
			//$newtemplateSource = preg_replace("/<(.*?)(href)\s*=\s*(\'|\")(\#.*)(\'|\")(.*?)>/si", "<$1$2=$3".__Routing::scriptUrl()."$4$5$6>", $templateSource);
			// if ( !empty( $newtemplateSource ) )
			// 			{
			// 				$templateSource = &$newtemplateSource;
			// 			}
		}
		return $templateSource;
	}
}
