<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

class pinax_dataAccessDoctrine_types_Time extends \Doctrine\DBAL\Types\TimeType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof pinax_types_Time) {
            return $value;
        }

        $val = pinax_types_Time::createFromFormat('!'.$platform->getTimeFormatString(), $value);
        if ( ! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getTimeFormatString());
        }
        return $val;
    }
}
