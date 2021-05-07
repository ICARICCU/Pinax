<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_languages_models_proxy_LanguagesProxy extends pinaxcms_contents_models_proxy_ActiveRecordProxy
{
    private static $defaultLanguageId;

    public function save($data)
    {
        $isNew = !(intval($data->__id) && $data->__id > 0);
        $isDefault = $data->language_isDefault;
        $currentDefaultId = 0;
        $countryId = $data->language_FK_country_id;
        if ($countryId) {
            $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Country');
            if ($ar->load($countryId)) {
                $data->language_code = $ar->country_639_1;
            }
        } else {
            $data->language_code = '';
        }

        if (!$data->language_order) {
            $data->language_order = 1;
        }

        // if isDefault read the current defautl language
        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
        if ($ar->find(array('language_isDefault' => 1))) {
            $currentDefaultId = $ar->language_id;
        }

        if (!$isDefault && $currentDefaultId==$data->__id) {
            return array(__T('You can\'t remove the default proerty, to do it set the default to other record'));
        } else if ($isDefault && $currentDefaultId!=$data->__id) {
            $ar->language_isDefault = 0;
            $ar->save();
        }

        if ($isNew && !$currentDefaultId) {
            return array(__T('Can\'t create a new language if there aren\'t a default one'));
        }

        $result = parent::save($data);

        if ($isNew && $currentDefaultId) {
            $this->duplicateMenu($currentDefaultId, $result['__id']);
            $this->duplicateMedia($currentDefaultId, $result['__id']);
        }
    }


    public function delete($recordId, $model)
    {
        $this->deleteMenu($recordId);
        $this->deleteContents($recordId);
        $this->deleteMedia($recordId);
        parent::delete($recordId, 'pinaxcms.core.models.Language');
    }

    private function duplicateMenu($languageId, $newLanguageId)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $it->load('duplicateMenuDetail', array(':languageId' => $languageId, ':newLanguageId' => $newLanguageId));
        $it->exec();
    }

    private function duplicateContents($languageId, $newLanguageId)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $it->load('duplicateDocumentsDetail', array(':languageId' => $languageId, ':newLanguageId' => $newLanguageId));
        $it->exec();
    }

    private function duplicateMedia($languageId, $newLanguageId)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $it->load('duplicateMediaDetail', array(':languageId' => $languageId, ':newLanguageId' => $newLanguageId));
        $it->exec();
    }

    private function deleteMenu($languageId)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $it->load('deleteMenuDetail', array('languageId' => $languageId));
        $it->exec();
    }

    private function deleteContents($languageId)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $it->load('deleteDocumentsDetail', array('languageId' => $languageId));
        $it->exec();
    }

    private function deleteMedia($languageId)
    {
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
        $it->load('deleteMediaDetail', array('languageId' => $languageId));
        $it->exec();
    }

    public function getLanguageId()
    {
        $editingLanguageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
        if (!is_null($editingLanguageId)) {
            return $editingLanguageId;
        } else {
            return pinax_ObjectValues::get('org.pinax', 'languageId');
        }
    }

    public function getDefaultLanguageId()
    {
        if (!__Session::exists('pinax.default.languageId')) {
            $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
            if (!__Config::get('MULTILANGUAGE_ENABLED')) {
            	$ar->resetSiteField();
            }

	    $ar->find(array('language_isDefault' => 1));
            __Session::set('pinax.default.language', $ar->language_code);
            __Session::set('pinax.default.languageId', $ar->language_id);
            return $ar->language_id;
        }

        return __Session::get('pinax.default.languageId');
    }

    public function findLanguageByCountry($languageCountryId, $id)
    {
        $ar = pinax_ObjectFactory::createModel('pinaxcms.core.models.Language');
        if (!__Config::get('MULTILANGUAGE_ENABLED')) {
            $ar->resetSiteField();
        }

        $r = $ar->find(array('language_FK_country_id' => $languageCountryId));
        return $r && $ar->language_id!=$id;
    }
}
