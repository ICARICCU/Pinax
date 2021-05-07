<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_plugins_Search extends pinax_plugins_PluginServer
{
	function run($params)
	{
    	$this->runClients($params);

		return $this->getResult();
	}

	function addResult($result)
	{
		$result['__weight__'] = str_pad( $result['__weight__'], 6, "0", STR_PAD_LEFT ).$result['title'];
		$this->_output[] = $result;
	}

	function getResultStructure()
	{
		$result = array();
		$result['title']	= '';
		$result['date']	= '';
		$result['dateOrd']	= '';
		$result['description']	= '';
		$result['__url__'] 		= '';
		$result['__weight__'] 	= 0;
	}
}
