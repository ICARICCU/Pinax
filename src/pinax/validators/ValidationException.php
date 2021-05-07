<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_validators_ValidationException extends Exception
{
    /**
     * @var array
     */
    private $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
        parent::__construct(sprintf("There were validation errors: %s", implode("\n", $this->errors)));
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
