<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_models_proxy_MenuFindTermProxy
{
    function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $oldMultisite =  __Config::get('MULTISITE_ENABLED');
        __Config::set('MULTISITE_ENABLED', false);

        if ($proxyParams && property_exists($proxyParams, 'filterType')) {
            $filterType = $proxyParams->filterType;
        }
        $selfId = __Request::get('menu_id');
        $languageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu');

            $it->load('autocompletePagePicker', array('search' => '%'.$term.'%', 'languageId' => $languageId, 'menuId' => '', 'pageType' => $filterType));


        $result = array();
        foreach($it as $ar) {
            if ($selfId==$ar->menu_id) {
                continue;
            }

            $result[] = array(
                'id' => $ar->menu_id,
                'text' => $ar->menudetail_title
            );
        }

        __Config::set('MULTISITE_ENABLED', $oldMultisite);
        return $result;
    }
}
