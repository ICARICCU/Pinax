<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_rest_core_Exception extends \Exception
{
    protected $httpStatus;

    /**
     * @param string $message
     * @param integer $httpStatus
     * @param integer $code
     */
    public function __construct($message = '', $httpStatus = 500, $code = 0) {
        parent::__construct($message, $code);
        $this->httpStatus = $httpStatus;
    }

    /**
     * @return integer
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }
}
