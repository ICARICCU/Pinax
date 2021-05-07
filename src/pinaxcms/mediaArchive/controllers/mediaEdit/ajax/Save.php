<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_mediaEdit_ajax_Save extends pinax_mvc_core_CommandAjax
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    protected $mediaIds = array();

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $data = json_decode($data);

        if (__Config::get('pinaxcms.mediaArchive.mediaMappingEnabled') && $data->mediaFileServer) {
            $result = $this->saveMediasFromServer($data);
            return $this->processResult($result);
        } else if (property_exists($data, 'medias')) {
            $result = $this->saveMedias($data);
            return $this->processResult($result);
        } else if ($data->media_id){
            try {
                $result = $this->modifyMedia($data);
                if (is_array($result)) {
                    $this->directOutput = true;
                }

                return $result;
            } catch (Exception $e){
                $this->directOutput = true;
                $errors = method_exists($e, 'getErrors') ? $e->getErrors() : [$e->getMessage()];
                return [
                    'errors' => $errors
                ];
            }

        } else {
            $this->directOutput = true;
            return array('evt' => array('No medias'));
        }
    }

    protected function processResult($result)
    {
        $this->directOutput = true;
        if (is_array($result) && $result['errors']) {
            return $result;
        } else {
            $application = &pinax_ObjectValues::get('org.pinax', 'application');
            $url = pinax_helpers_Link::makeUrl( $this->getRedirectUrl(), array( 'pageId' => $application->getPageId() ) );
            return array('url' => $url);
        }
    }

    public function saveMediasFromServer($data)
    {
        $file_path = $data->mediaFileServer;
        $file_virtual_path = preg_replace('/\//', '://', $file_path, 1);

        $application = &pinax_ObjectValues::get('org.pinax', 'application');
        $mappingService = $application->retrieveProxy('pinaxcms.mediaArchive.services.MediaMappingService');
        $file_path = $mappingService->getRealPath($file_path);
        $file_name = pathinfo($file_path, PATHINFO_BASENAME);

        $media = new StdClass();
        foreach ($data as $k => $v) {
            $media->$k = $v;
        }
        $media->media_fileName = $file_virtual_path;
        $media->__filePath = $file_path;
        $media->__originalFileName = $file_name;

        $mediaProxy = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.models.proxy.MediaProxy');
        $currentMediaId = $mediaProxy->saveMedia($media, $data->copyToCMS == 'true' ? $mediaProxy::COPY_TO_CMS : $mediaProxy::NONE);
        array_push($this->mediaIds,$currentMediaId);
        return $currentMediaId;
    }

    public function saveMedias($data)
    {
        $data->medias = is_array($data->medias) ? $data->medias : pinax_helpers_Convert::formEditObjectToStdObject($data->medias);

        $uploadFolder = __Paths::get('UPLOAD');
        $mediaProxy = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.models.proxy.MediaProxy');
        $medias = $data->medias;

        if (!count($medias)) {
            return array('errors' => array(__T('Nessun file caricato')));
        }

        $r = $this->checkDuplicates($medias);
        if ($r!==false) {
            return $r;
        }

        $medias = $this->decompressFiles($medias);

        $uploadedFiles = 0;
        foreach ($medias as $media) {
            if (!$media->__uploadFilename) continue;

            $media->__filePath = realpath($uploadFolder.$media->__uploadFilename);
            try {
                $result = $mediaProxy->saveMedia($media);
            } catch (Exception $e) {
                var_dump($e->getErrors());
            }

            if (is_array($result) && $result['errors']) {
                return $result;
            }

            array_push($this->mediaIds, $result);
            $uploadedFiles++;
        }

        if (!$uploadedFiles) {
            return array('errors' => array(__T('Nessun file caricato')));
        }

        return true;
    }

    public function modifyMedia($data)
    {
        $media = pinax_ObjectFactory::createModel('pinaxcms.models.Media');
        if(!$media->load($data->media_id)){
            throw new Exception(__T('Error, the record does not exists'));
        }

        $media->media_modificationDate = new pinax_types_DateTime();

        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$media->fieldExists($k)) continue;
            $media->$k = $v;
        }

        $media->media_FK_user_id = pinax_ObjectValues::get('org.pinax', 'userId');

        $result = $media->media_id;

        if (property_exists($data, 'mediaToReplace') ) {
            $data->mediaToReplace = is_array($data->mediaToReplace) ? $data->mediaToReplace : pinax_helpers_Convert::formEditObjectToStdObject($data->mediaToReplace);
        } else {
            $data->mediaToReplace = [];
        }

        if (count($data->mediaToReplace) === 1) {
            $this->replaceMedia($media, $data->mediaToReplace[0]->__uploadFilename, $data->mediaToReplace[0]->__originalFileName);
            $result = ['url' => $this->changePage('actionsMVC', ['action' => 'edit', 'id' => $media->media_id])];
        }

        if(!$media->save()){
            throw new Exception(__T('Error during save'));
        }

        return $result;
    }

    public function getRedirectUrl()
    {
        return 'pinaxcmsMediaArchiveAdd';
    }

    private function checkDuplicates($medias)
    {
        $uploadFolder = __Paths::get('UPLOAD');

        // controlla se il file esiste già nell'archivio
        $ar = pinax_ObjectFactory::createModel('pinaxcms.models.Media');
        if ($ar->getField('media_md5')) {
            foreach($medias as $media) {
                if (!$media->__uploadFilename) continue;

                $md5 = md5_file(realpath($uploadFolder.$media->__uploadFilename));

                $ar->emptyRecord();
                $result = $ar->find(array('media_md5' => $md5));

                if ($result) {
                    return array('errors' => array(__T('File already in media archive', $media->__originalFileName)));
                }
            }
        }
        return false;
    }

    private function decompressFiles($medias)
    {
        if (!count($medias) || !property_exists($medias[0], '__expand')) {
            return $medias;
        }

        $properties = array_keys(get_object_vars($medias[0]));
        if (!in_array('media_title', $properties)) {
            $properties[] = 'media_title';
        }
        $tempMedias = [];
        $uploadFolder = __Paths::get('UPLOAD');
        foreach ($medias as $media) {
            if (!$media->__uploadFilename) continue;
            if ($media->__expand==1) {
                $unzipper  = new VIPSoft\Unzip\Unzip();

                $destFolder = $uploadFolder.$media->__uploadFilename.md5(time());
                $filenames = $unzipper->extract($uploadFolder.$media->__uploadFilename, $destFolder);
                foreach($filenames as $f) {
                    if (strpos($f, '__MACOSX')!==false || strpos($f, '.DS_Store')!==false) continue;
                    $filename = $destFolder.'/'.$f;
                    if (is_dir($filename)) continue;
                    $newMedia = clone $media;
                    $info = pathinfo($filename);
                    $newMedia->__uploadFilename = substr($filename, strlen($uploadFolder));
                    $newMedia->__originalFileName = $info['basename'];
                    $newMedia->media_title = str_replace(array('_', '-'), ' ', $info['filename']);
                    $tempMedias[] = $newMedia;
                }
            } else {
                $tempMedias[] = $media;
            }
        }

        return $tempMedias;
    }

    /**
     * @param pinaxcms.models.Media $media
     * @param string $uploadFilename
     * @param string $originalFileName
     * @return void
     */
    private function replaceMedia($media, $uploadFilename, $originalFileName){
        if(!$this->validateMediaType($originalFileName, $media->media_type)){
            throw new Exception(__T('Different media type'));
        }

        $uploadFolder = __Paths::get('UPLOAD');
        $mediaProxy = pinax_ObjectFactory::createObject('pinaxcms.mediaArchive.models.proxy.MediaProxy');
        $result = $mediaProxy->copyFileInArchive(pinaxcms_mediaArchive_models_proxy_MediaProxy::MOVE_TO_CMS,
            $uploadFolder.$uploadFilename,
            $originalFileName,
            $media->media_type);

        if(!$result['status']){
            throw new Exception(__T('Error during copy'));
        }

        $mediaObj = pinaxcms_mediaArchive_MediaManager::getMediaByRecord($media);
        $oldFileName = $mediaObj->getFileName();

        if (file_exists($oldFileName) && $mediaObj->getIconFileName() != $oldFileName) {
            unlink($oldFileName);
        }

        $media->media_originalFileName = $originalFileName;
        $media->media_fileName = $result['destName'];
        $media->media_md5 = md5_file($result['destPath']);
    }

    /**
     * @param string $originalFileName
     * @param string $oldFileType
     * @return boolean
     */
    private function validateMediaType($originalFileName, $oldFileType){
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $fileType = pinaxcms_mediaArchive_MediaManager::getMediaTypeFromExtension($fileExtension);
        return $fileType === $oldFileType;
    }
}
