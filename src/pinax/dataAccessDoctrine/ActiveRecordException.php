<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_ActiveRecordException extends Exception
{
    public static function getFailed($tableName, $field)
    {
        return new self('Undefined property via __get(), table: '. $tableName .' field: '. $field);
    }

    public static function setFailed($tableName, $field)
    {
        return new self('Undefined property via __set(), table: '. $tableName .' field: '. $field);
    }

    public static function primaryKeyNotDefined($tableName)
    {
        return new self('Undefined primary key on table '. $tableName);
    }

    public static function detailPrimaryKeyNotDefined($tableName)
    {
        return new self('Undefined primary key on detail table '. $tableName);
    }

    public static function primaryKeyAlreadyDefined($tableName)
    {
        return new self('Primary key is already defined on table '. $tableName);
    }

    public static function detailPrimaryKeyAlreadyDefined($tableName)
    {
        return new self('Detail primary key is already defined on table '. $tableName);
    }

    public static function undefinedField($tableName, $field)
    {
        return new self('Undefined field "'.$field.'" in model "'. $tableName.'"');
    }

}
