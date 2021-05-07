<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_plugins_PluginManager extends PinaxObject
{
	function addPlugin($name, $class)
	{
		$name = str_replace('_', '.', strtolower($name));
		$pluginsInfo = &pinax_ObjectValues::get('pinax.plugins.PluginManager', 'pluginsInfo', array());
		if (!isset($pluginsInfo[$name]))
		{
			$pluginsInfo[$name] = array();
		}
		$pluginsInfo[$name][] = $class;
	}

	static function getPluginChain($name)
	{
		$name = strtolower($name);
		$pluginsInfo = &pinax_ObjectValues::get('pinax.plugins.PluginManager', 'pluginsInfo', array());
		if (!isset($pluginsInfo[$name]))
		{
			$pluginsInfo[$name] = array();
		}
		return $pluginsInfo[$name];
	}

	static function processPluginChain($name, &$parent, $params)
	{
		$pluginsInfo = pinax_plugins_PluginManager::getPluginChain($name);
		foreach ($pluginsInfo as $plugin)
		{
			$pluginObj = &pinax_ObjectFactory::createObject($plugin);
			$pluginObj->run($parent, $params);
		}
	}
}
