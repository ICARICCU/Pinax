<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_plugins_PluginServer extends PinaxObject
{
	var $_output = array();


	function runClients($params)
	{
		$className = str_replace('_', '.', $this->getClassName());
		pinax_plugins_PluginManager::processPluginChain($className, $this, $params);
	}

	function run($params)
	{
	}

	function getResultStructure()
	{
		$result = array();
		return $result;
	}

	function addResult($result)
	{
		$this->_output[] = $result;
	}

	function getResult()
	{
		return $this->_output;
	}
}
