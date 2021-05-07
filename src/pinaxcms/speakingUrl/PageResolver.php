<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_PageResolver extends pinaxcms_speakingUrl_AbstractUrlResolver implements pinaxcms_speakingUrl_IUrlResolver
{
    private $multilanguage;

    public function __construct()
    {
        parent::__construct();
        $this->type = 'pinaxcms.core.models.Content';
        $this->protocol = 'internal:';
        $this->multilanguage = __Config::get('MULTILANGUAGE_ENABLED');
    }

    public function compileRouting($ar)
    {
        $language = $this->multilanguage ? $ar->language_code.'/' : '';
        return '<pnx:Route skipLanguage="true" value="'.$language.$ar->speakingurl_value.'" pageId="'.$ar->speakingurl_FK.'" language="'.$ar->language_code.'"/>';
    }

    /**
     * @param string $term
     * @param string $id
     * @param string $protocol
     * @param $filter
     * @return array
     */
    public function searchDocumentsByTerm($term, $id, $protocol='', $filter=[])
    {
        $result = array();
        if ($protocol && $protocol!=$this->protocol) return $result;

        $languageId = $this->editLanguageId;
        $it = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Menu');

        if ($term) {
            $it->load('autocompletePagePicker', array('search' => '%'.$term.'%', 'languageId' => $languageId, 'menuId' => '', 'pageType' => $filter['pageType'], 'menuType' => $filter['menuType']));
        } else if ($id) {
            if (!is_numeric($id) && strpos($id, $this->protocol) !== 0) {
                return $result;
            } elseif (is_string($id)) {
                $id = $this->getIdFromLink($id);
            }

            $it->load('autocompletePagePicker', array('search' => '', 'languageId' => $languageId, 'menuId' => $id, 'pageType' => $filter['pageType'], 'menuType' => $filter['menuType']));
        }  else  {
            return $result;
        }

        foreach($it as $ar) {
            $result[] = array(
                'id' => $this->protocol.$ar->menu_id,
                'text' => $ar->menudetail_title,
                'path' => ltrim($ar->p1.'/'.$ar->p2.'/'.$ar->p3, '/').'/'.$ar->menudetail_title.' ('.__T($ar->menu_pageType).')'
            );
        }

        return $result;
    }

    public function makeUrl($id)
    {
        $resolvedVO = $this->resolve($id);
        return $resolvedVO ? $resolvedVO->url : false;
    }

    public function makeLink($id)
    {
        $resolvedVO = $this->resolve($id);
        return $resolvedVO ? $resolvedVO->link : false;
    }

    public function resolve($id)
    {
        $info = $this->extractProtocolAndId($id);
        if ($info->protocol.':' === $this->protocol && is_numeric($info->id)) {
            return $this->createResolvedVO($info->id, $info->queryString);
        }
        return false;
    }

    public function makeUrlFromRequest()
    {
        $id = __Request::get('pageId', __Config::get('START_PAGE'));
        $resolvedVO = $this->createResolvedVO($id);
        return $resolvedVO->url;
    }

    protected function createResolvedVO($id, $queryString='')
    {
        $siteMap = $this->application->getSiteMap();
        $menu = $siteMap->getNodeById($id);

        if ($menu) {
            $menuUrl = $menu->url;
            $menuTitle = $menu->title;

            if ($menuUrl) {
                $url = preg_match('/^http|https/', $menuUrl) ? $menuUrl : PNX_HOST . '/' . $menuUrl;
            } else {
                $url = __Link::makeUrl('link', ['pageId' => $id, 'title' => $menuTitle]);
            }
            $url .= $queryString;

            $resolvedVO = pinaxcms_speakingUrl_ResolvedVO::create(
                        $menu,
                        $url,
                        __Link::makeSimpleLink($menuTitle, $url),
                        $menuTitle
                    );
            return $resolvedVO;
        }

        // the menu isn't found or isn't visible in this language
        // redirect to home
        return $this->createResolvedVO(__Config::get('START_PAGE'));
    }
}
