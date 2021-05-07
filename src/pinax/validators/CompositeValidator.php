<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_validators_CompositeValidator implements pinax_validators_ValidatorInterface
{
    /**
     * @var pinax_validators_ValidatorInterface[]
     */
    private $validators = array();

    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue, $values) {
        $errors = array();

        foreach ($this->validators as $validator) {
            $result = $validator->validate($description, $value, $defaultValue, $values);
            if (is_string($result)) {
                $errors[$description] = $result;
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @param pinax_validators_ValidatorInterface $validator
     */
    public function add($validator) {
        $this->validators[] = $validator;
    }

    /**
     * @param pinax_validators_ValidatorInterface[] $validators
     *
     * @return void
     */
    public function addArray($validators) {
        $this->validators = array_merge($this->validators, $validators);
    }
}
