<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_middleware_DynamicPage extends pinax_interfaces_Middleware
{
    public function beforeProcess($pageId, $pageType)
    {
    }

    public function afterRender($content)
    {
        $this->etag = md5($content.var_export(__Request::getAllAsArray(), true));
        $this->checkIfIsChanged();
    }
}
