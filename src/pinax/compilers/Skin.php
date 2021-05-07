<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_compilers_Skin extends pinax_compilers_Compiler
{
	function compile( $options )
	{
		$this->_cacheObj->save($options['defaultHtml']);
		return $this->_cacheObj->getFileName();
	}

	/**
	*	Verifica se il file è compilato, in casi affermativo restutuisce il path
	*/
	function verify($fileName, $options=NULL)
	{
		$cacheFileName = $this->_cacheObj->verify($fileName, get_class($this));

		if (!empty($options['defaultHtml']))
		{
			// memorizza la skin di defaul del componente
			// come file in cache per poi passarlo al template engine
			if ($cacheFileName===false)
			{
				$this->_fileName = $fileName;
				$cacheFileName = $this->compile( $options );
				if ($cacheFileName===false)
				{
					// TODO
					echo "FATAL ERROR ".$fileName;
				}
			}
		}

		return $cacheFileName;
	}

}
