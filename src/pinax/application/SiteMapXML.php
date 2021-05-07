<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_application_SiteMapXML extends pinax_application_SiteMap
{
    /**
     * @var string
     */
    var $_type = 'xml';

    /**
     * @var string
     */
    var $_source = NULL;

    /**
     * @var string
     */
    private $aclDefaultIfNoDefined;

    function __construct($source=NULL)
    {
        parent::__construct();
        $this->_source = is_null($source) ? pinax_Paths::getRealPath('APPLICATION', __Config::get('SITEMAP')) : $source;
        $this->aclDefaultIfNoDefined = __Config::get('pinax.acl.defaultIfNoDefined')===true ? 'true' : 'false';
    }

    /**
     * @return void
     */
    public function loadTree($forceReload=false)
    {
        if ($forceReload) $this->init();

        $application = &pinax_ObjectValues::get('org.pinax', 'application');
        $lang = $application->getLanguage();

        $options = array(
            'cacheDir' => pinax_Paths::get('CACHE_CODE'),
            'lifeTime' => __Config::get('CACHE_CODE'),
            'hashedDirectoryLevel' => __Config::get('CACHE_CODE_DIR_LEVEL'),
            'readControlType' => '',
            'fileExtension' => '.php'
        );
        $cacheObj = &pinax_ObjectFactory::createObject('pinax.cache.CacheFile', $options );
        $cacheFileName = $cacheObj->verify( $this->_source, get_class( $this ).'_'.$lang);

        if ( $cacheFileName === false )
        {
            $this->_processSiteMapXML( $this->_source );
            $customSource = preg_replace( '/.xml$/i', '_custom.xml', $this->_source );
            if ( file_exists( $customSource ) )
            {
                $this->_processSiteMapXML( $customSource );
            }

            $cacheObj->save( serialize( $this->_siteMapArray ), NULL, get_class( $this ).'_'.$lang);
            $cacheObj->getFileName();
        }
        else
        {
            $this->_siteMapArray = unserialize( file_get_contents( $cacheFileName ) );
        }

        $this->_makeChilds();
    }

    /**
     * @param null|string $fileName
     *
     * @return void
     */
    private function _processSiteMapXML( $fileName, $parentId = '' )
    {
        $application = &pinax_ObjectValues::get('org.pinax', 'application');
        $lang = $application->getLanguage();

        $modulesState = [];

        if (__Config::get('DB_TYPE')!=='none') {
            $modulesState = pinax_Modules::getModulesState();
        }

        $xmlString = file_get_contents( $fileName );
        if ( strpos( $xmlString, '<pnx:modulesAdmin />' ) )
        {
            $modulesSiteMap = '';
            $modules = pinax_Modules::getModules();
            foreach( $modules as $m )
            {
                $moduleDisabled = __Config::get($m->id) === false;
                if ( $m->enabled && $m->siteMapAdmin && !$moduleDisabled)
                {
                    $modulesSiteMap .= $m->siteMapAdmin;
                }
            }
            $xmlString = str_replace( '<pnx:modulesAdmin />', $modulesSiteMap, $xmlString );
        }

        $xml = pinax_ObjectFactory::createObject( 'pinax.parser.XML' );
        $xml->loadXmlAndParseNS( $xmlString );
        $pages = $xml->getElementsByTagName('Page');
        $total = $pages->length;
        $pagesAcl = array();

        for ($i = 0; $i < $total; $i++) {
            $currNode = $pages->item( $i );

            $nodeTitle = '';
            $this->_searchNodeDetails($currNode, $nodeTitle, $lang);

            $id = $currNode->getAttribute('id');
            if (isset($modulesState[$id]) && !$modulesState[$id]) continue;

            $menu                   = $this->getEmptyMenu();
            $menu['id']             = strtolower($id);
            $menu['parentId']       = $currNode->hasAttribute('parentId') ? strtolower($currNode->getAttribute('parentId') ) :
                                            ( $currNode->parentNode->hasAttribute('id') ? strtolower($currNode->parentNode->getAttribute('id')) : '' );
            $menu['pageType']       = $currNode->hasAttribute('pageType') ? $currNode->getAttribute('pageType') : $currNode->getAttribute('id');
            $menu['isPublished']    = 1;
            $menu['isVisible']      = $currNode->hasAttribute('visible') ?
                                            $this->updateVisibilityCode('', $currNode->getAttribute('visible')) : '';
            $menu['cssClass']       = $currNode->getAttribute('cssClass');
            $menu['icon']           = $currNode->getAttribute('icon');
            $menu['sortChild']      = $currNode->hasAttribute('sortChild') && $currNode->getAttribute('sortChild')=='true';
            $menu['hideInNavigation'] = $currNode->hasAttribute('hide') ? $currNode->getAttribute('hide')=='true' : false;

            if (!in_array($menu['isVisible'], array('true', 'false'))) {
                $newVisibility = '';
                $service = $menu['id'];
                $action = 'visible';
                if (($currNode->hasAttribute('adm:acl') && !$currNode->hasAttribute('adm:aclPageTypes')) || in_array($menu['id'], $pagesAcl) )
                {
                    $acl = $currNode->getAttribute('adm:acl');
                    $aclParts = explode( ',', $acl);
                    if (count($aclParts)==2 && strlen($aclParts[0])!=1 && strlen($aclParts[1])!=1 ) {
                        $service = $aclParts[0];
                        $action = $aclParts[1];
                    }
                    $newVisibility = '{php:$user.acl("'.$service.'", "'.$action.'", '.$this->aclDefaultIfNoDefined.')}';
                }
                else if ( $currNode->hasAttribute('adm:aclPageTypes') )
                {
                    $temp = array();
                    $aclPages = explode(',', strtolower($currNode->getAttribute('adm:aclPageTypes')));
                    foreach($aclPages as $service) {
                        $temp[] = '$user.acl("'.$service.'", "'.$action.'", '.$this->aclDefaultIfNoDefined.')';
                    }
                    $newVisibility = '{php:('.implode(' OR ', $temp).')}';
                }

                $menu['isVisible'] = $this->updateVisibilityCode($menu['isVisible'], $newVisibility);
            }

            $menu['title']             = $nodeTitle;
            $menu['depth']             = 1;
            $menu['childNodes']     = array();

            // solo admin
            $menu['order']             = 0;
            $menu['hasPreview']     = 0;
            $menu['type']             = 'PAGE';
            $menu['creationDate']         = 0;
            $menu['modificationDate']     = 0;
            $menu['url']                  = $currNode->getAttribute('url');
            if ( strpos( $menu['url'], '&' ) === 0 )
            {
                $menu['url'] = __Link::scriptUrl( true ).$menu['url'];
            }
            $menu['select']              = strtolower($currNode->getAttribute('select'));
            $menu['nodeObj']             = NULL;
            $menu['adm:acl']         = $currNode->hasAttribute('adm:acl') ? $currNode->getAttribute('adm:acl') : null;
            $menu['adm:aclLabel']   = $currNode->hasAttribute('adm:aclLabel') ? $currNode->getAttribute('adm:aclLabel') : null;
            $menu['adm:aclPageTypes']   = $currNode->hasAttribute('adm:aclPageTypes') ? $currNode->getAttribute('adm:aclPageTypes') : null;

            if ($menu['adm:aclPageTypes']) {
                $pagesAcl = array_merge(explode(',', strtolower($menu['adm:aclPageTypes'])), $pagesAcl);
            }

            $this->_siteMapArray[$menu["id"]] = $menu;
        }
    }

    /**
     * @param string $title
     */
    private function _searchNodeDetails(&$myNode, &$title, $language)
    {
        if ( $myNode->hasAttribute('value') )
        {
            $title = $myNode->getAttribute('value');
            if (preg_match("/\{i18n\:.*\}/i", $title))
            {
                $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $title);
                $title = pinax_locale_Locale::getPlain($code);
            }
            return $title;
        }

        foreach( $myNode->childNodes as $currNode )
        {
            if ( ( $currNode->nodeName=='Title' || $currNode->nodeName=='pnx:Title' ) && $currNode->getAttribute('lang')==$language)
            {
                $title= $currNode->hasAttribute('value') ? trim($currNode->getAttribute('value')) : trim($currNode->getText());
                break;
            }
        }
    }

    /**
     * @param  string  $currentValue
     * @param  string  $valueToAdd
     * @return string
     */
    private function updateVisibilityCode($currentValue, $valueToAdd)
    {
        if (in_array($valueToAdd, array('true', 'false'))) {
            return $valueToAdd;
        }

        $valueToAdd = $this->convertToPhp($valueToAdd);
        if ($valueToAdd && preg_match("/\{php\:.*\}/i", $currentValue)) {
            return substr($currentValue, 0, -1).' && '.substr($valueToAdd, 5);
        }

        return $currentValue ? $currentValue : $valueToAdd;
    }

    /**
     * @param  string $value
     * @return string
     */
    private function convertToPhp($value)
    {
        if (preg_match("/\{php\:.*\}/i", $value)) {
            return $value;
        }
        else if (preg_match("/\{config\:.*\}/i", $value))
        {
            $code = preg_replace("/\{config\:(.*)\}/i", "$1", $value);
            return '{php:__Config::get(\''.$code.'\')}';
        }

        return $value;
    }
}
