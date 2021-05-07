<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_models_proxy_ModuleContentProxy extends PinaxObject
{
    // restituisce true se è valido
    // altrimenti un array con gli errori di validazione
    public function validate($data, $model)
    {
        $document = pinax_ObjectFactory::createModel($model);

        try {
            $document->validate($data);
        } catch (pinax_validators_ValidationException $e) {
            return $e->getErrors();
        }

        return true;
    }

    public function loadContent($recordId, $model, $status='PUBLISHED')
    {
        $document = pinax_ObjectFactory::createModel($model);
        $document->disableVisibilityOnLoad();

        if ($recordId) {
            if (!$document->load($recordId, $status)) {
                $languageProxy = __ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
                $document->load($recordId, $status, $languageProxy->getDefaultLanguageId());
            }
        }
        $values = (array)$document->getValuesForced();

        if (__Config::get('ACL_MODULES')) {
            // caricamento permessi editing e visualizzazione record
            $ar = pinax_ObjectFactory::createModel('pinaxcms.contents.models.DocumentACL');
            $ar->load($recordId);

            $values['__aclEdit'] = $this->getPermissionName($ar->__aclEdit);
            $values['__aclView'] = $this->getPermissionName($ar->__aclView);
        }

        return $values;
    }

    private function getPermissionName($permissions)
    {
		$names = array();
		$permissions = explode(',', $permissions);
		$ar = pinax_ObjectFactory::createModel('pinaxcms.roleManager.models.Role');
		foreach ($permissions as $v) {
			if ($ar->load($v)) {
				$names[] = array (
                    'id' => $ar->role_id,
                    'text' => $ar->role_name
                );
			}
		}

		return $names;
	}

    /**
     * @param  Object  $data
     * @param  boolean $saveHistory
     * @param  boolean $draft
     * @param  boolean $saveCurrentPublished
     * @param  boolean $publishDraft
     * @return array
     */
    public function saveContent($data, $saveHistory=true, $draft=false, $saveCurrentPublished=false, $publishDraft=false)
    {
        $recordId = $data->__id;
        $model = $data->__model;

        $document = pinax_ObjectFactory::createModel($model);
        $document->disableVisibilityOnLoad();
        $result = $document->load($recordId, $draft ? 'DRAFT' : 'PUBLISHED');

        if (!$result) {
            $languageProxy = __ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
            $defaultLanguageId = $languageProxy->getDefaultLanguageId();
            $document->load($recordId, 'PUBLISHED_DRAFT', $defaultLanguageId);
            $document->setDetailFromLanguageId($languageProxy->getLanguageId());
        }

        if (property_exists($data, 'title')) {
            $document->title = $data->title;
        }

        if (property_exists($data, 'url')) {
            $document->url = $data->url;
        }

        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$document->fieldExists($k)) continue;
            $document->$k = $v;
        }
        $document->fulltext = pinaxcms_core_helpers_Fulltext::make($data, $document, pinaxcms_core_helpers_FulltextCmsFilter::mediaFilter());

        if (property_exists($data, 'document_detail_isVisible')) {
            $document->setVisible($data->document_detail_isVisible);
        }


        try {
            if ($saveCurrentPublished) {
                $id = $document->saveCurrentPublished();
            } else if (($saveHistory && !$draft) || $publishDraft===true) {
                $id = $document->publish();
                if (!$saveHistory) {
                    // delete all OLD
                    $document->deleteStatus('OLD');
                }
            } else if ($saveHistory && !$draft) {
                $id = $document->publish();
            } else if ($saveHistory && $draft) {
                $id = $document->saveHistory();
            } else if (!$saveHistory && !$draft) {
                $id = $document->save(null, false, 'PUBLISHED');
            } else if (!$saveHistory && $draft) {
                $id = $document->save(null, false, 'DRAFT');
            }

            if (__Config::get('ACL_MODULES')) {
                // gestione acl record
                $ar = pinax_ObjectFactory::createModel('pinaxcms.contents.models.DocumentACL');
                $ar->load($id);
                $ar->__aclEdit = $data->__aclEdit;
                $ar->__aclView = $data->__aclView;
                $ar->save();
            }
        }
        catch (pinax_validators_ValidationException $e) {
            return $e->getErrors();
        }

        return array('__id' => $id, 'document' => $document);
    }

    /**
     * @param int $recordId
     * @param string $model
     * @return void
     */
    public function delete($recordId, $model='')
    {
        if (__Config::get('ACL_MODULES')) {
            // cancella i permessi di editing e visualizzazione record
            // TODO gestire le relazioni in ActiveRecordDocument cosicché questo codice venga automaticamente gestito
            // dal delete sul document
            $ar = pinax_ObjectFactory::createModel('pinaxcms.contents.models.DocumentACL');
            $ar->load($recordId);
            $ar->__aclEdit = array();
            $ar->__aclView = array();
            $ar->save();
        }

        // cancella il document;
        $document = pinax_ObjectFactory::createModel(!$model ? 'pinaxcms.core.models.Content' : $model);
        $document->delete($recordId);
    }

    /**
     * @param int $recordId
     * @param string $model
     * @param int $language
     * @return void
     */
    public function deleteLanguage($recordId, $model='', $language)
    {
        $document = pinax_ObjectFactory::createModel(!$model ? 'pinaxcms.core.models.Content' : $model);
        if ($document->load($recordId, pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED, $language)) {
            $document->{pinax_dataAccessDoctrine_ActiveRecordDocument::DOCUMENT_DETAIL_TRANSLATED} = 0;
            $document->deleteLanguage();
        }
    }

    /**
     * @param int $recordId
     * @param string $model
     * @return void
     */
    public function toggleVisibility($recordId, $model='')
    {
        $document = pinax_ObjectFactory::createModel(!$model ? 'pinaxcms.core.models.Content' : $model);
        $document->disableVisibilityOnLoad();
        if ($document->load($recordId)) {
            $document->setVisible($document->isVisible() ? 0 : 1);
            $document->saveCurrentPublished();
        }
    }
}
