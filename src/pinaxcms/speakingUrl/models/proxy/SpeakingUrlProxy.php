<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_models_proxy_SpeakingUrlProxy extends PinaxObject
{
    public function validate($value, $languageId, $id, $type)
    {
        $ar = pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_value = $value;
        // TODO: perché non cercare su tutti i campi?
        if ($ar->find()) {
            return $ar->speakingurl_FK == $id && $ar->speakingurl_type == $type;
        }

        return true;
    }


    public function deleteUrl($languageId, $id, $type)
    {
        $ar = pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_FK = $id;
        $ar->speakingurl_type = $type;
        if ($ar->find()) {
            $ar->delete();
        }
    }

    public function addUrl($value, $languageId, $id, $type, $options=array())
    {
        $ar = pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_FK = $id;
        $ar->speakingurl_type = $type;
        if (!$ar->find()) {
            $ar->speakingurl_FK_language_id = $languageId;
            $ar->speakingurl_FK = $id;
            $ar->speakingurl_type = $type;
        }

        $ar->speakingurl_value = $value;
        $ar->speakingurl_option = serialize($options);
        $ar->save();
    }

    public function getUrlForId($id, $languageId)
    {
        $ar = pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_FK = $id;
        $r = $ar->find(array('speakingurl_FK_language_id' => $languageId, 'speakingurl_FK' => $id));
        return $r ? $ar : false;
    }

    public function getUrlByValueAndType($value, $type)
    {
        $ar = pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.SpeakingUrl');
        $r = $ar->find(array('speakingurl_value' => $value, 'speakingurl_type' => $type));
        return $r ? $ar : false;
    }

    public function deleteAllByType($type)
    {
        $it = pinax_ObjectFactory::createModelIterator('org.pinaxcms.speakingUrl.models.SpeakingUrl');
        $it->load('deleteAllByType', array(':type' => $type));
        $it->exec();
    }

    public function getModel()
    {
        return pinax_ObjectFactory::createModel('org.pinaxcms.speakingUrl.models.SpeakingUrl');
    }
}
