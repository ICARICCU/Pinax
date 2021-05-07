<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;

class pinax_dataAccessDoctrine_types_ArrayID extends pinax_dataAccessDoctrine_types_Array
{
    public function convertToDatabaseValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform = null)
    {
        return $value;
    }
}
