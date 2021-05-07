<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_validators_NotNull implements pinax_validators_ValidatorInterface
{
    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue, $values)
    {
        if ($value !== null || ($defaultValue !== '' && $defaultValue !== null)) {
            return true;
        }

        return $description . " non può essere null";
    }
}
