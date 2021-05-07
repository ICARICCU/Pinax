<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_models_proxy_ContentProxy extends PinaxObject
{
    /**
     * Return a object with the system property for read or save the content values
     * @return pinaxcms_contents_models_ContentVO
     */
    public function getContentVO()
    {
        $vo = pinax_ObjectFactory::createObject('pinaxcms.contents.models.ContentVO');
        return $vo;
    }

    /**
     * Read the content for a menu
     * @param  int  $menuId
     * @param  int  $languageId
     * @param  boolean $setMenuTitle
     * @param  string  $status
     * @param  boolean $editMode
     * @return pinaxcms_contents_models_ContentVO   Content
     */
    public function readContentFromMenu($menuId, $languageId, $setMenuTitle=true, $status='PUBLISHED', $editMode=false)
    {
        $menuDocument = $this->readRawContentFromMenu($menuId, $languageId, $status, $editMode);
        $contentVO = $menuDocument ? $menuDocument->getContentVO() : $this->getContentVO();

        if ($menuDocument && $contentVO->__status!=$status) {
            $contentVO = $this->getContentVO();
        }

        // il contenuto può non esserci
        // viene caricato il titolo e l'id dal menù
        $contentVO->setId($menuId);

        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $menu = $menuProxy->getMenuFromId($menuId, $languageId);
        if ($setMenuTitle){
            $contentVO->setTitle($menu->menudetail_title);
        }
        if (__Config::get('pinaxcms.speakingUrl')) {
            $contentVO->setUrl($menu->speakingurl_value);
        }

        return $contentVO;
    }

    public function availableContentFromMenu($menuId, $languageId)
    {
        $menuDocument = $this->readRawContentFromMenu($menuId, $languageId, pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED_DRAFT);

        $hasPublishedVersion = $menuDocument->hasPublishedVersion();
        $hasDraftVersion = $menuDocument->hasDraftVersion();

        if (is_null($hasPublishedVersion) && is_null($hasDraftVersion)) {
            $hasPublishedVersion = true;
        }

        return array(
                        'PUBLISHED' => $hasPublishedVersion,
                        'DRAFT' => $hasDraftVersion
                    );
    }

    /**
     * Read the content for a menu
     *
     * NOTA: per velocizzare non viene controllato se il menù esiste
     * quindi se il menù non esiste oppure se non ci sono contenuti
     * ritorna un oggetto nullo
     *
     * @param  int $menuId
     * @param  int $languageId
     * @param  string $status
     * @param  boolean $editMode
     * @return pinaxcms_contents_models_Content   Content
     */
    public function readRawContentFromMenu($menuId, $languageId, $status, $editMode=false)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Content');
        if ($status==pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED_DRAFT) {
            $it->setOptions(array('type' => $status, 'language' => $languageId));
        } else {
            $it->whereStatusIs($status);
            $it->whereLanguageIs($languageId, !$editMode);
        }
        $menuDocument = $it->where('id', $menuId)->first();
        if (!$menuDocument) {
            $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Content');
            $it->setOptions(array('type' => $status=='PUBLISHED' ? 'DRAFT' : 'PUBLISHED'));
            $it->whereLanguageIs($languageId, !$editMode);
            $menuDocument = $it->where('id', $menuId)->first();
        }
        if (!$menuDocument) {
            $menuDocument = pinax_ObjectFactory::createModel('pinaxcms.core.models.Content');
        }

        return $menuDocument;
    }

    /**
     * Save the content for a menu
     * @param  pinaxcms_contents_models_ContentVO $data       Content to save
     * @param  int  $languageId Language id
     * @param  boolean  $saveHistory    Publish or save
     * @param  boolean  $setMenuTitle
     * @param  boolean  $updateModificationDate
     * @param  boolean  $draft
     * @param  boolean  $publishDraft
     * @return mixed
     */
    public function saveContent(pinaxcms_contents_models_ContentVO $data, $languageId, $saveHistory=true, $setMenuTitle=true, $updateModificationDate=true, $draft=false, $publishDraft=false)
    {
        $speakingUrlProxy = __Config::get('pinaxcms.speakingUrl') ? pinax_ObjectFactory::createObject('org.pinaxcms.speakingUrl.models.proxy.SpeakingUrlProxy') : null;

        $menuId = (int)$data->getId();
        if (!$menuId) {
            throw pinaxcms_exceptions_ContentException::missingMenuId();
        }

        $invalidateSitemapCache = false;
        $menuDocument = $this->readRawContentFromMenu($menuId, $languageId, $draft ? 'DRAFT' : 'PUBLISHED', true);
        $originalUrl = $menuDocument->url;
        $menuDocument->setDataFromContentVO($data);

        if (
            ($speakingUrlProxy && $menuDocument->url && $originalUrl != $menuDocument->url) &&
            !$speakingUrlProxy->validate($menuDocument->url, $languageId, $menuId, 'pinaxcms.core.models.Content')
            ) {
            //valida l'url
            return __T('Url already used');
        }

        try {
            if (($saveHistory && !$draft) || $publishDraft===true) {
                $id = $menuDocument->publish(null, $data->getComment());
                if (!$saveHistory) {
                    // delete all OLD
                    $menuDocument->deleteStatus('OLD');
                }
            } else if ($saveHistory && $draft) {
                $id = $menuDocument->saveHistory(null, false, $data->getComment());
            } else if (!$saveHistory && !$draft) {
                $id = $menuDocument->save(null, false, 'PUBLISHED', $data->getComment());
            } else if (!$saveHistory && $draft) {
                $id = $menuDocument->save(null, false, 'DRAFT', $data->getComment());
            }
        }
        catch (pinax_validators_ValidationException $e) {
            return $e->getErrors();
        }

        if ($speakingUrlProxy && $originalUrl!=$menuDocument->url) {
            // aggiorna l'url parlante
            $speakingUrlProxy = pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.proxy.SpeakingUrlProxy');
            if ($menuDocument->url) {
                $speakingUrlProxy->addUrl($menuDocument->url, $languageId, $menuId, 'pinaxcms.core.models.Content');
            } else {
                $speakingUrlProxy->deleteUrl($languageId, $menuId, 'pinaxcms.core.models.Content');
            }
            $invalidateSitemapCache = true;
        }

        // aggiorna il titolo della pagina
        $menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        if ($updateModificationDate) {
            $menuProxy->touch($menuId, $languageId);
        }
        $menu = $menuProxy->getMenuFromId($menuId, $languageId);
        if ($setMenuTitle && $menu->menudetail_title != $menuDocument->title) {
            $menuProxy->rename($menuId, $languageId, $menuDocument->title);
        }

        if (strtolower($menu->menu_pageType) == 'alias') {
            $menuProxy->menuUrl($menuId, $languageId, 'alias:'.$data->link);
            $invalidateSitemapCache = true;
        }

        if ($invalidateSitemapCache) {
            $menuProxy->invalidateSitemapCache();
        }

        $evt = array('type' => pinaxcms_contents_events_Menu::SAVE_CONTENT, 'data' => array('document' => $menuDocument, 'menu' => $menu));
        $this->dispatchEvent($evt);

        return true;
    }


    public function deleteContent($menuId)
    {
        // cancella il contenuto del documento associato
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Content');
        $menuDocument = $it->load('getContentForMenu', array('menuId' => $menuId))->first();

        if ($menuDocument) {
            $menuDocument->delete();
        }

        if (__Config::get('pinaxcms.speakingUrl')) {
            $speakingUrlProxy = pinax_ObjectFactory::createObject('org.pinaxcms.speakingUrl.models.proxy.SpeakingUrlProxy');
            $speakingUrlProxy->deleteUrl(pinax_ObjectValues::get('org.pinax', 'editingLanguageId'), $menuId, 'pinaxcms.core.models.Content');
        }
    }

    /**
     * Duplicate a menu and its contents
     *
     * @param int $menuId      the menu id to copy
     * @param int $allBranch   copy all childs if true
     * @param int $parentId    the parentId of menu (optional)
     * @return int             new menu id
     */
     public function duplicateMenuAndContent($menuId, $allBranch = false, $parentId = null)
    {
        $languageId = __ObjectValues::get('org.pinax', 'editingLanguageId');
        $menuProxy = __ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');
        $duplicateId = $menuProxy->duplicateMenu($menuId, $languageId, $parentId);

        $menu = pinax_ObjectFactory::createModel('pinaxcms.core.models.Menu');
        foreach ($menu->getLanguagesId() as $langId) {
            $menu = $menuProxy->getMenuFromId($menuId, $langId);
            $title = trim(__T('Copy of').' '.$menu->menudetail_title);

            $contentVO = $this->readContentFromMenu($menuId, $langId, false, 'PUBLISHED', true);
            $contentVO->setId($duplicateId);
            $contentVO->setTitle($title);
            $contentVO->setUrl('');

            $duplicationMenuStorageDelegate = pinax_ObjectFactory::createObject(__Config::get('pinaxcms.duplicationMenuStorageDelegate'));
            $contentVO = $duplicationMenuStorageDelegate->contentDuplicationFix($contentVO);

            $this->saveContent($contentVO, $langId, false);
        }


        // duplica i figli
        $itMenus = $menuProxy->getChildMenusFromId($menuId, $languageId, false);
        foreach($itMenus as $subMenu) {
            if ($subMenu->menu_type =='BLOCK' || $allBranch)  {
                $this->duplicateMenuAndContent($subMenu->menu_id, $allBranch, $duplicateId);
            }
        }

        return $duplicateId;
    }


}
