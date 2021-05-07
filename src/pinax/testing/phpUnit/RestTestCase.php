<?php

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class pinax_testing_phpUnit_RestTestCase extends BaseTestCase
{
    /**
     * @param  string  $uri
     * @param  string  $token
     * @param  array  $headers
     * @return pinax_testing_phpUnit_TestResponse
     */
    public function get($uri, $token=null, array $headers = [])
    {
        if ($token) {
            $headers = array_merge($headers, ['Authorization' => 'Bearer '.$token]);
        }
        return $this->call('GET', $uri, $headers);
    }

    /**
     * @param  string  $uri
     * @param  array  $params
     * @param  string  $token
     * @param  array  $headers
     * @return pinax_testing_phpUnit_TestResponse
     */
    public function getWithParams($uri, array $params, $token=null, array $headers = [])
    {
        if ($token) {
            $headers = array_merge($headers, ['Authorization' => 'Bearer '.$token]);
        }
        $uri .= '?'.http_build_query($params);
        return $this->call('GET', $uri, $headers);
    }

    /**
     * @param  string  $uri
     * @param  array  $params
     * @param  string  $token
     * @param  array  $headers
     * @return pinax_testing_phpUnit_TestResponse
     */
    public function post($uri, array $params, $token=null, array $headers = [])
    {
        if ($token) {
            $headers = array_merge($headers, ['Authorization' => 'Bearer '.$token]);
        }
        return $this->call('POST', $uri, $headers, json_encode($params));
    }


    /**
     * @param  string  $uri
     * @param  array  $params
     * @param  string  $token
     * @param  array  $headers
     * @return pinax_testing_phpUnit_TestResponse
     */
    public function postFile($uri, array $params, array $files, $token=null, array $headers = [], string $originalfileName=null)
    {
        if ($token) {
            $headers = array_merge($headers, ['Authorization' => 'Bearer '.$token]);
        }

        $boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW';
        $headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;

        $boundary = '--'.$boundary;

        $content = [];
        $content[] = '';
        foreach($params as $name=>$value) {
            $content[] = $boundary;
            $content[] = 'Content-Disposition: form-data; name="'.$name.'"';
            $content[] = '';
            $content[] = $value;
        }

        foreach($files as $name=>$value) {
            $fileName = $originalfileName ? : basename($value);
            $content[] = $boundary;
            $content[] = 'Content-Disposition: form-data; name="'.$name.'"; filename="'.$fileName.'"';
            $content[] = 'Content-Type: application/octet-stream';
            $content[] = '';
            $content[] = file_get_contents($value);
        }
        $content[] = $boundary;

        return $this->call('POST', $uri, $headers, implode("\r\n", $content));
    }



    /**
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $headers
     * @return pinax_testing_phpUnit_TestResponse
     */
    private function call($method, $uri, array $headers, string $content = '')
    {
        if (!isset($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        $headers = array_reduce(array_keys($headers), function($carry, $item) use ($headers){
            $carry[] = $item.': '.$headers[$item];
            return $carry;
        }, []);

        $opts = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'content' => $content
            ],
        ];

        $context = stream_context_create($opts);
        $response = file_get_contents(sprintf('%s/%s', $_SERVER['API_HOST'], $uri), false, $context);

        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        $status = $match[1];
        return new pinax_testing_phpUnit_TestResponse($status, @json_decode($response));
    }
}
