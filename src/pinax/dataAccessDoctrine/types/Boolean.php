<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;

class pinax_dataAccessDoctrine_types_Boolean extends \Doctrine\DBAL\Types\BooleanType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        $value = $value==='false' ? 0 :
                    ($value==='true' ? 1 : $value);
        return parent::convertToDatabaseValue($value, $platform);
    }
}
