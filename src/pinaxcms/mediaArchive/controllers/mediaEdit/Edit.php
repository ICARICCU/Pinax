<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_mediaArchive_controllers_mediaEdit_Edit extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute()
    {
        $this->checkPermissionForBackend();

        $id = __Request::get('id');
        $mediaAr = pinax_ObjectFactory::createModel('pinaxcms.models.Media');
        $mediaAr->load($id);
        $data = $mediaAr->getValuesAsArray();

        $this->view->setData($data);

        // preview
        $mediaBridge = pinaxcms_Pinaxcms::getMediaArchiveBridge();
        $thumb_filename = $mediaBridge->imageByIdAndResizedUrl(
                                            $mediaAr->media_id,
                                            __Config::get('THUMB_WIDTH'),
                                            __Config::get('THUMB_HEIGHT'),
                                            __Config::get('ADM_THUMBNAIL_CROP'),
                                            __Config::get('ADM_THUMBNAIL_CROPPOS'));
        $this->setComponentsContent('preview',
                [
                    'thumb_filename' => $thumb_filename,
                    '__urlDownload__' => pinaxcms_helpers_Media::getFileUrlById($mediaAr->media_id),
                    '__urlPreview__' => pinaxcms_helpers_Media::getImageUrlById($mediaAr->media_id, 800, 600),
                ]);

        $c = $this->view->getComponentById('mediaToReplaceUploader');
        if ($c) {
            $c->setAttribute('data', ';maxlabel='.ini_get('upload_max_filesize').'B', true);
        }
    }
}
