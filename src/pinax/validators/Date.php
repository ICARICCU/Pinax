<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_validators_Date implements pinax_validators_ValidatorInterface
{
    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue, $values)
    {
        if (preg_match('/^[\d]{2,4}-[\d]{1,2}-[\d]{1,2}$/', $value) || empty($value)) {
            return true;
        }

            return $description . " deve essere una data";
    }
}
