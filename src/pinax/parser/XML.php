<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_parser_XML extends DOMDocument
{
	public $namespaces = array();
	private $defaultNamespaces = array ( 'xmlns:pnx' => '"pinax.components.*"', 'xmlns:model' => '"pinax.models.*"', 'xmlns:adm' => '"pinax.components.adm.*"' );

	public function load( $source, $options = NULL )
	{
		$this->checkIfFileExits( $source );
		$this->preserveWhiteSpace = false;
		$r = parent::load( $source, $options );

		return $r;
	}


	public function loadAndParseNS( $file )
	{
		$this->checkIfFileExits( $file );
		$xmlString = file_get_contents( $file );
		return $this->loadXmlAndParseNS( $xmlString );
	}


	public function loadXmlAndParseNS( $xmlString )
	{
		// esegue il parsing del primo nodo per ricavare i namespace definiti
		preg_match_all( '/<[^\?]*\s*[^>]*>/iU', $xmlString, $match );
		if ( count( $match[ 0 ] ) )
		{
			foreach( $match[ 0 ] as $m )
			{
				if ( strpos( $m, '<?' ) === false )
				{
					// controlla se sono presenti i namespace di default
					$rootNodeString =  $m;
					foreach( $this->defaultNamespaces as $ns => $uri )
					{
						$rootNodeString = $this->addDefaultNS( $rootNodeString, $ns, $uri );
					}
					$xmlString = str_replace( $m, $rootNodeString, $xmlString );

					preg_match_all( '/xmlns:(.*)[\s\\n\\r]*=[\s\\n\\r]*["\'](.*)["\']/iU', $rootNodeString, $matchns );
					$numNS = count( $matchns[ 0 ] );
					if ( $numNS )
					{
						for( $i = 0; $i < $numNS; $i++ )
						{
							$this->namespaces[ $matchns[ 1 ][ $i ] ] = $matchns[ 2 ][ $i ];
						}
					}

					break;
				}
			}
		}

		$this->preserveWhiteSpace = false;
		$r = $this->loadXML( $xmlString , LIBXML_NOERROR );
		return $r;
	}

	private function addDefaultNS( $text, $ns, $uri )
	{
		if ( stripos( $text, $ns ) === false )
		{
			$text = preg_replace( '/(\s|>)/', ' '.$ns.'='.$uri.'$1', $text, 1 );
		}
		return $text;
	}


	private function checkIfFileExits( $file )
	{
		if ( !file_exists( $file ) )
		{
			throw new Exception( 'File non esiste '.$file );
		}
	}
}
