<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinaxcms_template_fe_views_AbstractTemplate extends PinaxObject
{
    protected $path;

    abstract protected function fixTemplateName($view);

    public function render($application, $view, $templateData)
    {
        $siteProp = unserialize(pinax_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
        $view->addOutputCode($siteProp['title'], 'siteTitle');
        $view->addOutputCode($siteProp['subtitle'], 'siteSubtitle');
        $this->fixTemplateName($view);
    }
}
