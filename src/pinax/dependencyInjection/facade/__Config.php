<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class __Config {
    /**
     * @var pinax_interfaces_Config
     */
    private static $object = null;

    /**
     * @param pinax_interfaces_Config $object
     */
    public function __construct(pinax_interfaces_Config $object)
    {
        self::$object = $object;
    }

    /**
     * @param $code
     * @return mixed|null
     */
    public static function get($code)
    {
        return self::$object->get($code);
    }

    /**
     * @param $code
     * @param $value
     */
    public static function set($code, $value)
    {
        self::$object->set($code, $value);
    }


    public static function dump()
    {
        self::$object->dump();
    }


    /**
     * @return array
     */
    public static function getAllAsArray()
    {
        return self::$object->getAllAsArray();
    }


    /**
     * @param $modeName
     */
    public static function setMode( $modeName )
    {
        self::$object->setMode($modeName);
    }


    public static function destroy()
    {
        self::$object->destroy();
    }


    /**
     * @param  string $code
     * @return boolean
     */
    public static function exists($code)
    {
        return self::$object->exists($code);
    }

}
