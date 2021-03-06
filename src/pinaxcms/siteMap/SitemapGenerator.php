<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_siteMap_SitemapGenerator
{
    protected $sitemapxml;

    public function generate($applicationSiteMap)
    {
        $this->sitemapxml = $this->siteMapForPages($applicationSiteMap);

        $modules = pinax_Modules::getModules();
        $this->sitemapxml .= $this->siteMapForModules($applicationSiteMap, pinax_Modules::getModules());
    }

    public function finalize()
    {
        return $this->finalXml($this->sitemapxml);
    }

    public function write($xml, $fileName='sitemap.xml')
    {
        return file_put_contents($fileName, $xml);
    }

    /**
     * @return string
     */
    private function siteMapForPages($applicationSiteMap)
    {
        $sitemapxml = '';
        $siteMapIterator = pinax_ObjectFactory::createObject('pinax.application.SiteMapIterator', $applicationSiteMap);
        while (!$siteMapIterator->EOF) {
            $n = $siteMapIterator->getNode();
            $siteMapIterator->moveNext();
            if (!$n->isVisible ||
                $n->isLocked ||
                $n->hideInNavigation ||
                $n->pageType=='Empty' ||
                $n->pageType=='Alias' ||
                $n->type!='PAGE' ) continue;

            $url = $n->url ? PNX_HOST.'/'.$n->url : __Link::makeUrl('link', array('pageId' => $n->id));
            $sitemapxml .= $this->printNode($url);
        }

        return $sitemapxml;
    }

    /**
     * @param  pinax_application_SiteMap $applicationSiteMap
     * @param  array(pinax_ModuleVO)  $module
     * @return string
     */
    private function siteMapForModules($applicationSiteMap, $modules)
    {
        $sitemapxml = '';
        foreach( $modules as $m ) {
            if (!$m->pageType) continue;
            $menu = $applicationSiteMap->getMenuByPageType($m->pageType);
            if (!$menu->isVisible || $menu->isLocked) continue;
            $sitemapxml .= $this->siteMapForSingleModule($applicationSiteMap, $m);
        }

        return $sitemapxml;
    }

    /**
     * @param  pinax_application_SiteMap $applicationSiteMap
     * @param  pinax_ModuleVO  $module
     * @return string
     */
    private function siteMapForSingleModule($applicationSiteMap, $module)
    {
        $sitemapxml = '';
        $speakingUrlManager = __ObjectFactory::createObject('pinaxcms.speakingUrl.Manager');
        $urlResolver = $speakingUrlManager->getResolver($module->id);
        if (!$urlResolver) return $sitemapxml;

        $model = $urlResolver->modelName();
        $it = __ObjectFactory::createModelIterator($model);
        foreach($it as $ar) {
            if (!$ar->document_detail_isVisible) continue;
            $sitemapxml .= $this->printNode($urlResolver->makeUrlFromModel($ar));
        }

        return $sitemapxml;
    }

    /**
     * @param  string $url
     * @return string
     */
    protected function printNode($url)
    {
        return sprintf('<url><loc>%s</loc><changefreq>weekly</changefreq></url>', $url);
    }

    /**
     * @param  string $sitemapxml
     * @return string
     */
    private function finalXml($sitemapxml)
    {
        $xml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$sitemapxml
</urlset>
EOD;
        return $xml;
    }
}
