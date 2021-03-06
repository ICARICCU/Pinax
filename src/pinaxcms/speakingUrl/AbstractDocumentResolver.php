<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_AbstractDocumentResolver extends pinaxcms_speakingUrl_AbstractUrlResolver
{
    protected $model;
    protected $pageType;
    protected $modelName;

    protected function getIdFromLink($id)
    {
        return str_replace($this->protocol, '', $id);
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


        if ($term) {
            $it = pinax_ObjectFactory::createModelIterator($this->model)->load('All');

            if ($term != '') {
                $it->where('title', '%'.$term.'%', 'ILIKE');
            }
            $it->orderBy('title');

            foreach($it as $ar) {

                $result[] = array(
                    'id' => $this->protocol.$ar->document_id,
                    'text' => $ar->title,
                    'path' => $this->modelName
                );
            }
        } elseif ($id) {
            if (strpos($id, $this->protocol) !== 0) {
                return $result;
            }

            $ar = pinax_ObjectFactory::createModel($this->model);
            $ar->load($this->getIdFromLink($id));
            $result[] = array(
                'id' => $this->protocol.$ar->document_id,
                'text' => $ar->title,
                'path' => $this->modelName
            );
        }

        return $result;
    }

    public function makeUrl($id)
    {
        if (strpos($id, $this->protocol) === 0) {
            $id = $this->getIdFromLink($id);
            return $this->makeUrlFromId($id);
        } else {
            return false;
        }
    }


    public function makeLink($id)
    {
        if (strpos($id, $this->protocol) === 0) {
            $id = $this->getIdFromLink($id);
            return $this->makeUrlFromId($id, true);
        } else {
            return false;
        }
    }

    public function makeUrlFromRequest()
    {
        $id = __Request::get('document_id');
        return $this->makeUrlFromId($id);
    }

    private function makeUrlFromId($id, $fullLink=false)
    {
        $ar = pinax_ObjectFactory::createModel($this->model);
        if ($ar->load($this->getIdFromLink($id))) {
            $siteMap = $this->application->getSiteMap();
            $menu = $siteMap->getMenuByPageType($this->pageType);

            if ($ar->document_detail_isVisible && $ar->document_detail_translated && $menu && $menu->isVisible) {
                if ($ar->keyInDataExists('url') && $ar->url) {
                    $language = $this->application->getLanguage();
                    $url = PNX_HOST.'/'.$language.'/'.$ar->url;
                } else {
                    $url = __Link::makeUrl('movio_news', array(  'document_id' => $id,
                                                                 'title' => $ar->title));
                }

                return $fullLink ? __Link::makeSimpleLink($ar->title, $url) : $url;
            }
        }

        // document not found, isn't visible or isn't traslated
        // go to entity page or home
        $speakingUrlManager = $this->application->retrieveProxy('pinaxcms.speakingUrl.Manager');
        return $fullLink ? '' : $speakingUrlManager->makeUrl('internal:'.( $menu ? $menu->id : __Config::get('START_PAGE')));
    }
}
