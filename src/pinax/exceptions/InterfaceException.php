<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_exceptions_InterfaceException extends Exception
{
    public static function notImplemented($interfaceName, $className)
    {
        return new self('Interface '.$interfaceName.' not implemented in class '.$className);
    }
}
