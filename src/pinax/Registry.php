<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_Registry
{
    /**
     * @param string $path
     * @param null $defaultValue
     *
     * @return null
     */
    public static function get($path, $defaultValue=NULL)
	{
		$params	= &pinax_Registry::_getValuesArray();

		if (array_key_exists($path, $params)) return $params[$path];

		$rs = pinax_ObjectFactory::createModel('pinax.models.Registry');
		$rs->registry_path = $path;
		if ($rs->find())
		{
			$params[$path] = $rs->registry_value;
			return $rs->registry_value;
		} else {
            $params[$path] = $defaultValue;
            return $defaultValue;
        }
	}

	/**
	 * @param string $path
	 * @param string $value
	 * @return void
	 */
	public static function set($path, $value)
	{
		$params	= &pinax_Registry::_getValuesArray();
		$params[$path] = $value;
		/** @var $rs pinax_dataAccessDoctrine_AbstractActiveRecord */
		$rs = pinax_ObjectFactory::createModel('pinax.models.Registry');
		$rs->find(array('registry_path' => $path));
		$rs->registry_path 	= $path;
		$rs->registry_value = $value;
		$rs->save();
	}

	/**
	 * @param string $path
	 * @param string $value
	 * @return void
	 */
	public static function add($path, $value)
	{
		self::set($path, $value);
	}

	/**
	 * @param string $path
	 * @return void
	 */
	public static function remove($path)
	{
		$params	= &pinax_Registry::_getValuesArray();

		if (array_key_exists($path, $params)) unset($params[$path]);
		/** @var $rs pinax_dataAccessDoctrine_AbstractActiveRecord */
		$rs = pinax_ObjectFactory::createModel('pinax.models.Registry');
		$rs->registry_path = $path;
		if ($rs->find()) $rs->delete();
	}

	/**
	 * @param string $path
	 * @return array
	 */
	public static function query($path)
	{
		$params	= &pinax_Registry::_getValuesArray();
		/** @var $rs pinax_dataAccessDoctrine_AbstractRecordIterator */
		$iterator = pinax_ObjectFactory::createModelIterator('pinax.models.Registry', 'all', array('filters' => array('registry_path' => $path)));
		$result = array();
		foreach ($iterator as $ar)
		{
			$params[$ar->registry_path] = $ar->registry_value;
			$result[$ar->registry_path] = $ar->registry_value;
		}

		return $result;
	}

	/**
	 * @param boolean $init
	 * @return array
	 */
	private static function &_getValuesArray($init=false)
	{
		static $_valuesArray = array();
		return $_valuesArray;
	}
}

