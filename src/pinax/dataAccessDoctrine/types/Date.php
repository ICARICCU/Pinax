<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;

class pinax_dataAccessDoctrine_types_Date extends \Doctrine\DBAL\Types\DateType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        return pinax_localeDate2ISO($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform = null)
    {
        if ($value == '0000-00-00') {
            return '';
        }

        return $value && is_string($value) ? pinax_defaultDate2locale(__T('PNX_DATE_FORMAT'), $value) : $value;
    }
}
