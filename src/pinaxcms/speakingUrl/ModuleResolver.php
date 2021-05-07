<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_ModuleResolver extends pinaxcms_speakingUrl_AbstractUrlResolver implements pinaxcms_speakingUrl_IUrlResolver
{
    protected $moduleVO;
    protected $moduleName;
    protected $model;
    protected $routingUrl;
    protected $titleField;

    public function __construct($moduleVO, $routingUrl, $modelName=null, $titleField='title')
    {
        parent::__construct();
        $this->moduleVO = $moduleVO;
        $this->type = $moduleVO->id;
        $this->protocol = $moduleVO->id.':';
        $this->moduleName = $moduleVO->name;
        $this->model = $modelName ? : $moduleVO->id.'.models.Model';
        $this->routingUrl = $routingUrl;
        $this->titleField = $titleField;
    }

    public function compileRouting($ar)
    {
        return '';
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

        $languageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');

        if ($term) {
            $it = pinax_ObjectFactory::createModelIterator($this->model)
                    ->load('all')
                    ->orderBy($this->titleField);

            $it->where($this->titleField, '%'.$term.'%', 'ILIKE');

            if (__Config::get('pinaxcms.content.defaultLanguageIfNotAvailable')) {
                $it->whereLanguageIs(__ObjectValues::get('org.pinax', 'languageId'), false);
            }

            foreach($it as $ar) {
                $result[] = array(
                    'id' => $this->protocol.$ar->document_id,
                    'text' => $ar->{$this->titleField},
                    'path' => $this->moduleName
                );
            }

        } else if ($id && strpos($id, $this->protocol) === 0) {
            $ar = pinax_ObjectFactory::createModel($this->model);
            $id = $this->getIdFromLink($id);

            if (!$ar->load($id) and __Config::get('pinaxcms.content.defaultLanguageIfNotAvailable')) {
                $languageProxy = __ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
                $defaultLanguageId = $languageProxy->getDefaultLanguageId();

                $ar->load($id, 'PUBLISHED', $defaultLanguageId);
            }

            $result[] = array(
                    'id' => $this->protocol.$ar->document_id,
                    'text' => $ar->{$this->titleField},
                    'path' => $this->moduleName
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

    // TODO implementare, non va bene per i moduli
    public function makeUrlFromRequest()
    {
        $id = __Request::get('id', null);
        $resolvedVO = $this->createResolvedVO($id);
        return $resolvedVO->url;
    }

    protected function createResolvedVO($id, $queryString='')
    {
        $ar = pinax_ObjectFactory::createModel($this->model);
        $result = $ar->load($id);

        if (!$result and __Config::get('pinaxcms.content.defaultLanguageIfNotAvailable')) {
            $languageProxy = __ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
            $defaultLanguageId = $languageProxy->getDefaultLanguageId();

            $result = $ar->load($id, 'PUBLISHED', $defaultLanguageId);
        }

        if ($id and $result) {
            $url = (__Link::makeUrl($this->routingUrl, array('document_id' => $id, $this->titleField => $ar->{$this->titleField}))).$queryString;
            $link = __Link::makeSimpleLink($ar->{$this->titleField}, $url);

            $resolvedVO = pinaxcms_speakingUrl_ResolvedVO::create(
                                    $ar,
                                    $url,
                                    $link,
                                    $ar->{$this->titleField}
                                );
            return $resolvedVO;
        }

        // TODO implementare, non va bene per i moduli
        // // the menu isn't found or isn't visible in this language
        // // redirect to home
        // return $this->makeUrlFromId(__Config::get('START_PAGE'), $fullLink);
        return pinaxcms_speakingUrl_ResolvedVO::create404();
    }

    public function modelName()
    {
        return $this->model;
    }

    public function makeUrlFromModel($model)
    {
        return __Link::makeUrl($this->routingUrl, array('document_id' => $model->getId(), $this->titleField => $model->{$this->titleField}));
    }
}
