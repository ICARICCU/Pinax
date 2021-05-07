<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_middleware_StaticPage extends pinax_interfaces_Middleware
{
    protected $etag;
    protected $lastModifiedTime;

    public function beforeProcess($pageId, $pageType)
    {
        $fileName = pinax_Paths::getRealPath('APPLICATION_PAGE_TYPE').$pageType.'.xml';
        $this->lastModifiedTime = filemtime($fileName);
        $this->etag = md5_file($fileName);

        $this->checkIfIsChanged();
    }

    public function afterRender($content)
    {
    }
}
