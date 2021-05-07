<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_compilers_LayoutManagerPHP extends pinax_compilers_Compiler
{
	function compile($options)
	{
		$this->_cacheObj->save($options);
		return $this->_cacheObj->getFileName();
	}

	/**
	*	Verifica se il file è compilato, in casi affermativo restituisce il path
	*/
	function verify($fileName, $options=NULL)
	{
		$cacheFileName = $this->_cacheObj->verify($fileName, get_class($this));
		return $cacheFileName;
	}

}
