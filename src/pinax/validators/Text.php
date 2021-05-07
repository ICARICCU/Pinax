<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_validators_Text implements pinax_validators_ValidatorInterface
{
    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue, $values)
    {
        if (is_string($value) || empty($value)) {
            return true;
        }

        return $description . " deve essere una stringa";
    }
}
