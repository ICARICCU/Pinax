<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_services_PageTypeService extends PinaxObject
{
    protected $source = null;
    protected $pageTypesMap = array();

	public function __construct($fileName = 'pageTypes.xml')
	{
		$this->source = __Paths::get('APPLICATION').'config/'.$fileName;

        if (file_exists($this->source)) {
    		$options = array(
    			'cacheDir' => pinax_Paths::get('CACHE_CODE'),
                'lifeTime' => __Config::get('CACHE_CODE'),
                'hashedDirectoryLevel' => __Config::get('CACHE_CODE_DIR_LEVEL'),
    			'readControlType' => '',
    			'fileExtension' => '.php'
    		);
    		$cacheObj = &pinax_ObjectFactory::createObject('pinax.cache.CacheFile', $options );
    		$cacheFileName = $cacheObj->verify( $this->source, get_class( $this ) );

    		if ( $cacheFileName === false )
    		{
    			$this->loadXml();
    			$cacheObj->save( serialize( $this->pageTypesMap ), NULL, get_class( $this ) );
    			$cacheObj->getFileName();
    		}
    		else
    		{
    			$this->pageTypesMap = unserialize( file_get_contents( $cacheFileName ) );
            }
		}
	}

    function onRegister() {

    }

    private function loadXml() {
        $xml = pinax_ObjectFactory::createObject('pinax.parser.XML');
        $xml->loadAndParseNS($this->source);
        $pageTypes = $xml->getElementsByTagName('pageType');

        $this->pageTypesMap = array();

        foreach ($pageTypes as $pageType) {
            $name = $pageType->getAttribute('name');
            $this->pageTypesMap[$name] = array (
                                        'name' => $name,
                                        'label' => $pageType->hasAttribute('label') ? __T($pageType->getAttribute('label')) : $pageType->getAttribute('name'),
                                        'class' => $pageType->getAttribute('class'),
                                        'unique' => $pageType->hasAttribute('unique') ? $pageType->getAttribute('unique') == 'true' : false,
                                        'acceptParent' => $pageType->hasAttribute('acceptParent') ? $pageType->getAttribute('acceptParent') : '',
                                        'isBlock' => $pageType->hasAttribute('isBlock') ? $pageType->getAttribute('isBlock') == 'true' : false
                                    );
        }
    }

    public function getAllPageTypes()
    {
        return $this->pageTypesMap;
    }
}
