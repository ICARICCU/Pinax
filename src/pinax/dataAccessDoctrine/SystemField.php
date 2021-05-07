<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_SystemField extends pinax_dataAccessDoctrine_DbField
{
    function __construct($name, $type, $size, $key, $validator, $defaultValue, $readFormat=true, $virtual=false, $description='', $index=self::INDEXED)
    {
        parent::__construct($name, $type, $size, $key, $validator, $defaultValue, $readFormat, $virtual, $description, $index);
        $this->isSystemField = true;
    }
}
