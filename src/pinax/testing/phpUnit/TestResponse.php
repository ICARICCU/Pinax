<?php

class pinax_testing_phpUnit_TestResponse
{
    /**
     * @var int
     */
    public $statusCode;

    /**
     * @var object
     */
    public $response;

    /**
     * @param int $statusCode
     * @param object $response
    */
    public function __construct($statusCode, $response)
    {
        $this->statusCode = $statusCode;
        $this->response = $response;
    }
}
