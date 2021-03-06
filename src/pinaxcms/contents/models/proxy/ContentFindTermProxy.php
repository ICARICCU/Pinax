<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_models_proxy_ContentFindTermProxy
{
    function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $oldMultisite =  __Config::get('MULTISITE_ENABLED');
        if ($proxyParams && property_exists($proxyParams, 'multisite') && !$proxyParams->multisite) {
             __Config::set('MULTISITE_ENABLED', false);
        }
        $document = pinax_ObjectFactory::createObject('pinax.dataAccessDoctrine.ActiveRecordDocument');
        $document->addField(new pinax_dataAccessDoctrine_DbField($fieldName, Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));

        $it = $document->createRecordIterator()
                ->select('index0.document_index_text_value')
                ->where('document_type', 'pinaxcms.content')
                ->groupBy('index0.document_index_text_value')
                ->allStatuses();

        if ($term != '') {
            $it->where($fieldName, '%'.$term.'%', 'LIKE');
        }

        $it->orderBy($fieldName);

        $result = array();
        foreach($it as $ar) {
            $result[] = array(
                'id' => $ar->document_index_text_value,
                'text' => $ar->document_index_text_value
            );
        }

        __Config::set('MULTISITE_ENABLED', $oldMultisite);
        return $result;
    }
}
