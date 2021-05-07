<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_interfaces_Config
{
    /**
     * @param $code
     * @return mixed|null
     */
    public function get($code);

    /**
     * @param $code
     * @param $value
     */
    public function set($code, $value);

    public function dump();

    /**
     * @return array
     */
    public function getAllAsArray();

    /**
     * @param $modeName
     */
    public function setMode( $modeName );

    public function destroy();

    /**
     * @param  string $code
     * @return boolean
     */
    public function exists($code);
}
