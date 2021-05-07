<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_types_DateTime extends DateTime
{
    public static function createFromFormat($format, $time, $object = NULL)
    {
        return DateTime::createFromFormat($format, $time, $object);
    }

    public function __toString()
    {
        return $this->format('Y-m-d H:i:s');
    }
}
