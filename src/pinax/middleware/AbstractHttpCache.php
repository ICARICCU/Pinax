<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_middleware_AbstractHttpCache implements pinax_interfaces_Middleware
{
    protected $etag;
    protected $lastModifiedTime;

    protected function checkIfIsChanged() {
        $this->setEtag();

        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false);
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        if (($this->lastModifiedTime && $ifModifiedSince == $this->lastModifiedTime) || ($etagHeader == $this->etag)) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }

    protected function setEtag()
    {
        header("Cache-Control: private");
        header("Pragma:");
        header("Expires:");
        if ($this->lastModifiedTime) header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->lastModifiedTime).' GMT');
        header('Etag: '.$this->etag);
    }
}
