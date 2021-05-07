<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_cache_ActiveRecord extends PinaxObject
{
    private $data = NULL;

    function __construct($data)
    {
        $this->data = $data;
    }

    function getValuesAsArray()
    {
        return $this->data;
    }

    public function __get($name)
    {
        return @$this->data[$name];
    }
}
