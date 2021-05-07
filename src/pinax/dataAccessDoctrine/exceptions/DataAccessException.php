<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_exceptions_DataAccessException extends Exception
{
    public static function unknownColumn($name, $tableName)
    {
        return new self('Unknown column '.$name.' in table '.$tableName);
    }
}
