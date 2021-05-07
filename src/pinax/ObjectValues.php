<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_ObjectValues
{
    /**
	 * @param string $className
	 * @param string $name
	 * @param null|mixed $defaultValue
	 *
	 * @return mixed
	 */
	public static function &get($className, $name='', $defaultValue=NULL)
	{
		$name = $name.'@'.$className;
		if ( empty( $className ) ) return $defaultValue;
		$params	= &pinax_ObjectValues::_getValuesArray();
		if (!isset($params[$name])) $params[$name] = $defaultValue;
		return $params[$name];
	}

    /**
	 * @param string $className
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return void
	 */
	public static function set($className, $name='', $value)
	{
		$name = $name.'@'.$className;
		$params	= &pinax_ObjectValues::_getValuesArray();
		$params[$name] = $value;
	}

    /**
	 * @param string $className
	 * @param string $name
	 * @param object $value
	 *
	 * @return void
	 */
	public static function setByReference($className, $name='', &$value)
	{
		$name = $name.'@'.$className;
		$params	= &pinax_ObjectValues::_getValuesArray();
		$params[$name] = &$value;
	}


    /**
     * @param string $className
     * @param string $name
     * @return bool
     */
	public static function exists($className, $name='')
	{
		$name = $name.'@'.$className;
		$params	= &pinax_ObjectValues::_getValuesArray();
		return isset($params[$name]);
	}


    /**
	 * @param string $className
	 * @param string $name
	 *
	 * @return void
	 */
	public static function remove($className, $name='')
	{
		$name = $name.'@'.$className;
		$params	= &pinax_ObjectValues::_getValuesArray();
		if (array_key_exists($name, $params))
		{
			unset($params[$name]);
		}
	}

	/**
	 * @return void
	 */
    public static function removeAll()
	{
		$params	= &pinax_ObjectValues::_getValuesArray();
		$params = array();
	}

	/**
	 * @return void
	 */
	public static function dump()
	{
		$params	= &pinax_ObjectValues::_getValuesArray();
		var_dump($params);
	}

	/**
	 * @return void
	 */
	public static function dumpKeys()
	{
		$params	= &pinax_ObjectValues::_getValuesArray();
		var_dump(array_keys($params));
	}

    /**
     * @param bool $init
     *
     * @return array
     */
    private static function &_getValuesArray($init=false)
	{
		static $_valuesArray = array();
		return $_valuesArray;
	}
}
