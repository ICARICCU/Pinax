<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;

class pinax_dataAccessDoctrine_types_Array extends \Doctrine\DBAL\Types\ArrayType
{
    public function convertToPHPValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        if (is_object($value)) {
            return get_object_vars($value);
        } else if (is_array($value)) {
            return $value;
        } else if (!$value) {
            return array();
        }
        return parent::convertToPHPValue($value, $platform);
    }
}
