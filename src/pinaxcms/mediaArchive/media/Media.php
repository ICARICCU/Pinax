<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinaxcms_mediaArchive_media_Media extends PinaxObject
{
    public $id;
    public $fileName;
    public $size;
    public $type;
    public $title;
    public $description;
    public $creationDate;
    public $modificationDate;
    public $category;
    public $author;
    public $date;
    public $originalFileName;
    public $copyright;
    public $allowDownload;
    public $watermark;
    public $ar;
    public $zoom;

    private $remoteCacheLifetime;

    function __construct(&$ar)
    {
        if ( is_object( $ar ) )
        {
            $this->ar               = $ar;
            $this->id               = $ar->media_id;
            $this->fileName         = $ar->media_fileName;
            $this->size             = $ar->media_size;
            $this->type             = $ar->media_type;
            $this->title            = pinax_encodeOutput($ar->media_title);
            $this->description      = $ar->media_description;
            $this->creationDate     = $ar->media_creationDate;
            $this->modificationDate = $ar->media_modificationDate;
            $this->category         = $ar->media_category;
            $this->author           = $ar->media_author;
            $this->date             = $ar->media_date;
            $this->zoom             = $ar->media_zoom;
            $this->originalFileName = $ar->media_originalFileName ? $ar->media_originalFileName : $ar->media_fileName;
            $this->copyright        = $ar->media_copyright;
            $this->allowDownload   = $ar->media_allowDownload;
            $this->watermark        = $ar->media_watermark;
        }
        else
        {
            $this->id               = $ar['media_id'];
            $this->fileName         = $ar['media_fileName'];
            $this->size             = $ar['media_size'];
            $this->type             = $ar['media_type'];
            $this->title            = pinax_encodeOutput($ar['media_title']);
            $this->creationDate     = $ar['media_creationDate'];
            $this->modificationDate = $ar['media_modificationDate'];
            $this->category         = $ar['media_category'];
            $this->author           = $ar['media_author'];
            $this->date             = $ar['media_date'];
            $this->zoom             = $ar['media_zoom'];
            $this->originalFileName = !empty($ar['media_originalFileName']) ? $ar['media_originalFileName'] : $ar['media_fileName'];
            $this->copyright        = $ar['media_copyright'];
            $this->allowDownload   = $ar['media_allowDownload'];
            $this->watermark        = $ar['media_watermark'];
        }

        $this->remoteCacheLifetime = __Config::get('pinax.media.image.remoteCache.lifetime');
    }

    function isMapped()
    {
        return preg_match('/([^:]+):\/\/(.+)/', $this->fileName);
    }

    function getFileName( $checkIfExists=true )
    {
        $file = $this->resolveFileName();

        if ( !$checkIfExists ) {
            return $file;
        } else {
            return file_exists($file) ? $file : pinax_Assets::get('ICON_MEDIA_IMAGE');
        }
    }

    function exists()
    {
        $file = $this->resolveFileName();
        return file_exists($file);
    }

    function getIconFileName()
    {
        return pinax_Assets::get('ICON_MEDIA_IMAGE');
    }

    function getResizeImage($width, $height, $crop=false, $cropOffset=1, $forceSize=false, $returnResizedDimension=true, $usePiramidalSizes = true)
    {
        return array('imageType' => IMG_JPG, 'fileName' => $this->getIconFileName(), 'width' => NULL, 'height' => NULL, 'originalWidth'=> NULL, 'originalHeight'=>  NULL);
    }

    function getThumbnail( $width, $height, $crop=false, $cropOffset = 0 )
    {
        $iconPath = $this->getIconFileName();
        // controlla se c'è un'anteprima associata
        if ( !empty( $this->ar->media_thumbFileName ) )
        {
            // TODO: da implementare meglio in modo che i metodi di resize
            // non siano in Image ma comuni a tutti i media
            $this->ar->media_fileName = $this->ar->media_thumbFileName;
            $this->ar->media_type = 'IMAGE';
            $media = pinaxcms_mediaArchive_MediaManager::getMediaByRecord( $this->ar );
            return $media->getThumbnail( $width, $height );
        }
        list( $originalWidth, $originalHeight, $imagetypes ) = getImageSize($iconPath);
        return array('fileName' => $iconPath, 'width' => $originalWidth, 'height' => $originalHeight);
    }

    function addDownloadCount()
    {
        $this->ar->media_download++;
        $this->ar->save();
    }

    private function resolveFileName()
    {
        if ($this->isRemoteFile()) {
            $file = $this->remoteMediaCacheFileName();
            return $this->retrieveRemoteMedia($file) ? $file : false;
        }

        // gestione mapping delle cartelle
        if (__Config::get('pinaxcms.mediaArchive.mediaMappingEnabled') && preg_match('/([^:]+):\/\/(.+)/', $this->fileName, $m)) {
            $application = pinax_ObjectValues::get('org.pinax', 'application' );
            if ($application) {
                $mappingService = $application->retrieveProxy('pinaxcms.mediaArchive.services.MediaMappingService');
            } else {
                $mappingService = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.services.MediaMappingService');
            }
            $targetPath = $mappingService->getMapping($m[1]);
            $this->fileName = $targetPath.'/'.$m[2];
        }

        if ( strpos( $this->fileName, '/' ) === false ) {
            $file = pinax_Paths::get('APPLICATION_MEDIA_ARCHIVE').ucfirst(strtolower($this->type)).'/'.$this->fileName;
        } else {
            $file = $this->fileName;
        }

        return $file;
    }

    private function retrieveRemoteMedia($fileName)
    {
        if (file_exists($fileName) && time()-filemtime($fileName) < $this->remoteCacheLifetime) {
            return true;
        }

        $folder = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!file_exists($folder)) {
            @mkdir($folder, 0755, true);
        }

        try {
            $remoteFileHandle = fopen($this->fileName, 'rb');
            $localFileHandle = fopen($fileName, 'wb');
            while ($buffer = fread($remoteFileHandle, 64*1024)) {
                fwrite($localFileHandle, $buffer);
            }

            if (is_resource($remoteFileHandle)) fclose($remoteFileHandle);
            if (is_resource($localFileHandle)) fclose($localFileHandle);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function remoteMediaCacheFileName()
    {
        return pinax_Paths::get('CACHE_IMAGES').'/media/'.md5($this->fileName);
    }

    /**
     * @return boolean
     */
    public function isRemoteFile()
    {
       return preg_match('/^(http:|https:)/', $this->fileName);
    }

    /**
     * @return string
     */
    public function getFileNameOrRemoteUrl()
    {
       return $this->isRemoteFile() ? $this->fileName : $this->getFileName();
    }

}
