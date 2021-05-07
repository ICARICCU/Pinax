<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_compilers_Config extends pinax_compilers_Compiler
{
	private $_config;
	private $_modes;

	function compile($options)
	{
		$this->initOutput();

		// esegue il parsing del file di configurazione
		$this->_config = array();
		$this->_modes = '$configArray[\'__modes__\'] = array()'.PNX_COMPILER_NEWLINE;
		$this->_compileXml($this->_fileName);

		foreach ($this->_config as $name=>$value)
		{
			$this->output .= '$configArray[\''.$name.'\'] = '.$value.PNX_COMPILER_NEWLINE;
		}
		$this->output .= $this->_modes;

		return $this->save();
	}

	function _compileXml($fileName)
	{
		$dirPath = dirname($fileName).'/';
		libxml_use_internal_errors( true );
		$xml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
		$xml->load( $fileName );
	    $errors = libxml_get_errors();
		if (!empty($errors)) {
			throw new Exception(json_encode($errors));
		}
		foreach( $xml->documentElement->childNodes as $nc )
		{
			$this->_compileXmlNode( $nc, $dirPath );
		}
	}

	function _compileXmlNode(&$node, $dirPath)
	{
		switch ( strtolower( $node->nodeName ) )
		{
			case 'pnx:import':
			case 'import':
				$appName = isset($_SERVER['PINAX_APPNAME']) ? $_SERVER['PINAX_APPNAME'] : '';
				$envName = getenv('PINAX_SERVER_NAME');
                $serverName = !isset( $_SERVER["SERVER_NAME"] ) ? (($appName ? :$envName) ? :'console') : $_SERVER["SERVER_NAME"];
				$src = str_replace('##HOST##', $serverName, $node->getAttribute('src'));

				if ($src=='##APPLICATION_TO_ADMIN##') {
					$src = '../'.pinax_Paths::get('APPLICATION_TO_ADMIN').'config/';

                    $configName = '';
    				if (isset($_SERVER['PINAX_APPNAME'])) {
						$serverName = $_SERVER['PINAX_APPNAME'];
						$configName = 'config_'.$serverName.'.xml';
						if ( !file_exists( realpath($dirPath.$src.$configName) ) ) {
                            $configName = '';
						}
					}

					if (!$configName) {
						$configName = 'config_'.$serverName.'.xml';
					}

					if ( !file_exists( realpath($dirPath.$src.$configName) ) )
					{
						$configName = 'config.xml';
					}
					$src .= $configName;
				}

				$importRealPath = realpath($dirPath.$src);

				if ($importRealPath === false) {
					throw new Exception($this->_fileName.PHP_EOL.' sta importando un file non esistente '.$dirPath.$src);
				}

				$this->_compileXml(realpath($dirPath.$src));
				break;

			case 'pnx:param':
			case 'param':
				$name 	= $node->getAttribute('name');
				$value 	= $node->hasAttribute('value') ? $node->getAttribute('value') : $node->firstChild->nodeValue;
				$value = str_replace('##ROOT##', pinax_Paths::get('ROOT'), $value);

				if ($value=="false") $value = false;
				else if($value=="true") $value = true;

				$this->_config[$name] = $value;

				if (gettype($value)=='string')
				{
					$this->_config[$name] = '\''.addcslashes($value, '\'').'\'';
				}
				else
				{
					$this->_config[$name] = $value ? 'true' : 'false';
				}
				break;
			case 'pnx:configmode':
			case 'configmode':
				$modeName 	= $node->getAttribute('name');
				$tempConfig = $this->_config;
				$this->_config = array();
				foreach ($node->childNodes as $n)
				{
					$this->_compileXmlNode($n, $dirPath);
				}

				$this->_modes .= '$configArray[\'__modes__\'][\''.$modeName.'\'] = array()'.PNX_COMPILER_NEWLINE;
				foreach ($this->_config as $name=>$value)
				{
					$this->_modes .= '$configArray[\'__modes__\'][\''.$modeName.'\'][\''.$name.'\'] = '.$value.PNX_COMPILER_NEWLINE;
				}
				$this->_config = $tempConfig;
				if ( $node->getAttribute('default') == "true" )
				{
					$this->_modes .= '__Config::setMode(\''.$modeName.'\')'.PNX_COMPILER_NEWLINE;
				}

				break;
		}


	}

	/**
	*	Verifica se il file � compilato, in casi affermativo restutuisce il path
	*/
	function verify($fileName, $options=NULL)
	{
		$cacheFileName = $this->_cacheObj->verify($fileName, get_class($this));
		if ($cacheFileName===false)
		{
			$this->_fileName = $fileName;
			$cacheFileName = $this->compile($fileName);
		}

		return $cacheFileName;
	}
}
