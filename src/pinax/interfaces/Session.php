<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_interfaces_Session
{
    /**
     * @return void
     */
    public function stop();

    /**
     * @return void
     */
    public function destroy();


    /**
     * @param string     $key
     * @param mixed $defaultValue
     * @param bool $readFromParams
     * @param bool $writeDefaultValue
     * @return mixed
     */
    public function get($key, $defaultValue=NULL, $readFromParams=false, $writeDefaultValue=false);

    /**
     * @param $key string
     * @param $value mixed
     */
    public function set($key, $value);

    /**
     * @param $key string
     * @return bool
     */
    public function exists($key);

    /**
     * @param $key string
     */
    public function remove($key);

    /**
     * @param  string $keyPrefix
     * @return void
     */
    public function removeKeysStartingWith($keyPrefix);

    /**
     * @return void
     */
    public function removeAll();

    /**
     * @return array
     */
    public function getAllAsArray();

    /**
     * @param array $values
     * @return void
     */
    public function setFromArray($values);

    /**
     * @return string
     */
    public function getSessionId();

    /**
     * @return void
     */
    public function dump();
}
