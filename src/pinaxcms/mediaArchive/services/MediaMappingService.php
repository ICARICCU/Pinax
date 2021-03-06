<?php
class pinaxcms_mediaArchive_services_MediaMappingService extends PinaxObject
{
    protected $init = false;
    protected $source = null;
    protected $fileSystemMap = array();

    public function __construct()
    {
    }

    public function init()
    {
        if (isset($_SERVER['PINAX_APPNAME'])) {
            $serverName = $_SERVER['PINAX_APPNAME'];
            $filename = 'filesystem_'.$serverName.'.xml';
        }
        else {
            $filename = 'filesystem.xml';
        }

        $this->source = __Paths::get('APPLICATION_TO_ADMIN').'config/'.$filename;

        if (!file_exists($this->source)) {
            $this->source = __Paths::get('APPLICATION').'config/'.$filename;
        }

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
            $cacheObj->save( serialize( $this->fileSystemMap ), NULL, get_class( $this ) );
            $cacheObj->getFileName();
        }
        else
        {
            $this->fileSystemMap = unserialize( file_get_contents( $cacheFileName ) );
        }

        $this->init = true;
    }

    function checkInit()
    {
        if (!$this->init) {
            $this->init();
        }
    }

    function onRegister() {

    }

    private function invalidate()
    {
        $options = array(
            'cacheDir' => pinax_Paths::get('CACHE_CODE'),
            'lifeTime' => __Config::get('CACHE_CODE'),
            'hashedDirectoryLevel' => __Config::get('CACHE_CODE_DIR_LEVEL'),
            'readControlType' => '',
            'fileExtension' => '.php'
        );
        $cacheObj = &pinax_ObjectFactory::createObject('pinax.cache.CacheFile', $options );
        $cacheObj->remove( $this->source, get_class( $this ) );
    }

    private function loadXml()
    {
        $xml = pinax_ObjectFactory::createObject('pinax.parser.XML');
        $xml->loadAndParseNS($this->source);
        $folders = $xml->getElementsByTagName('Folder');

        $this->fileSystemMap = array();

        foreach ($folders as $folder) {
            $this->fileSystemMap[$folder->getAttribute('name')] = $folder->getAttribute('target');
        }
    }

    private function saveMappingXML($name, $target)
    {
        $xml = pinax_ObjectFactory::createObject('pinax.parser.XML');
        $xml->loadAndParseNS($this->source);

        $xpath = new DOMXpath($xml);
        $elements = $xpath->query('/pnx:FileSystem/pnx:Folder[@name="'.$name.'"]');

        if ($elements->length == 1) {
            $folder = $elements->item(0);
        }
        else {
            $folder = $xml->createElement('pnx:Folder');
            $folder->setAttribute('name', $name);
            $root = $xml->childNodes->item(0);
            $root->appendChild($folder);
        }

        $folder->setAttribute('target', $target);

        $xml->formatOutput = true;
        $xml->save($this->source);

        $this->invalidate();
    }

    private function deleteMappingXML($name)
    {
        $xml = pinax_ObjectFactory::createObject('pinax.parser.XML');
        $xml->loadAndParseNS($this->source);

        $xpath = new DOMXpath($xml);
        $elements = $xpath->query('/pnx:FileSystem/pnx:Folder[@name="'.$name.'"]');

        if ($elements->length == 1) {
            $folder = $elements->item(0);
            $root = $xml->childNodes->item(0);
            $root->removeChild($folder);

            $xml->formatOutput = true;
            $xml->save($this->source);

            $this->invalidate();
        }
    }

    // esempio di filePath: folder1/folder2/filename
    // dove folder1 ?? la cartella mappata da risolvere
    public function getRealPath($filePath)
    {
        $slashPos = strpos($filePath, '/');
        $folderName = substr($filePath, 0, $slashPos);
        $target = substr($filePath, $slashPos);
        return $this->getMapping($folderName) . $target;
    }

    public function getMapping($name)
    {
        $this->checkInit();
        return $this->fileSystemMap[$name];
    }

    public function getPathFromMapping ($map) {
        if (__Config::get('pinaxcms.mediaArchive.mediaMappingEnabled')) {
            $m = explode('/', $map, 2);
            $application = pinax_ObjectValues::get('org.pinax', 'application' );
            if ($application) {
                $mappingService = $application->retrieveProxy('pinaxcms.mediaArchive.services.MediaMappingService');
            } else {
                $mappingService = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.services.MediaMappingService');
            }
            $targetPath = $mappingService->getMapping($m[0]);
            $map = $m[1] ? $targetPath.'/'.$m[1] : $targetPath;
        }
        return $map;
    }

    public function setMapping($name, $target)
    {
        $this->checkInit();
        $this->saveMappingXML($name, $target);
        return $this->fileSystemMap[$name] = $target;
    }

    public function deleteMapping($name)
    {
        $this->checkInit();
        $this->deleteMappingXML($name);
        unset($this->fileSystemMap[$name]);
    }

    public function getAllMappings()
    {
        $this->checkInit();
        return $this->fileSystemMap;
    }
}
