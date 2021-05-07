<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class __Session {
    /**
     * @var pinax_interfaces_Session
     */
    private static $object = null;

    /**
     * @param pinax_interfaces_Session $object
     */
    public function __construct(pinax_interfaces_Session $object)
    {
        self::$object = $object;
    }

    /**
     * @return void
     */
    public static function stop() {
        self::$object->stop();
    }

    /**
     * @return void
     */
    public static function destroy() {
        self::$object->destroy();
    }

    /**
     * @param string     $key
     * @param mixed $defaultValue
     * @param bool $readFromParams
     * @param bool $writeDefaultValue
     * @return mixed
     */
    public static function get($key, $defaultValue=NULL, $readFromParams=false, $writeDefaultValue=false)
    {
        return self::$object->get($key, $defaultValue, $readFromParams, $writeDefaultValue);
    }

    /**
     * @param $key string
     * @param $value mixed
     */
    public static function set($key, $value)
    {
        self::$object->set($key, $value);
    }

    /**
     * @param $key string
     * @return bool
     */
    public static function exists($key)
    {
        return self::$object->exists($key);
    }

    /**
     * @param $key string
     */
    public static function remove($key)
    {
        self::$object->remove($key);
    }

    /**
     * @param  string $keyPrefix
     * @return void
     */
    public static function removeKeysStartingWith($keyPrefix)
    {
        self::$object->removeKeysStartingWith($keyPrefix);
    }

    /**
     * @return void
     */
    public static function removeAll()
    {
        self::$object->removeAll();
    }

    /**
     * @return array
     */
    public static function getAllAsArray()
    {
        return self::$object->getAllAsArray();
    }

    /**
     * @param array $values
     * @return void
     */
    public static function setFromArray($values)
    {
        self::$object->setFromArray($values);
    }

    /**
     * @return string
     */
    public static function getSessionId()
    {
        return self::$object->getSessionId();
    }

    /**
     * @return void
     */
    public static function dump()
    {
        self::$object->dump();
    }
}
