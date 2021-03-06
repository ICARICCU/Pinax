<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_RecordSetList extends pinax_components_ComponentContainer
{
    protected $contentFromSkin = false;
    protected $routeUrl = array();

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('dataProvider',    true,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('cssClass',         false,    'even,odd',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('filters',        false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('useQueryParams',false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('paginate',        false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('query',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('routeUrl',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('title',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('processCell',     false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('processCellParams',    false,    NULL,        COMPONENT_TYPE_STRING);
        $this->defineAttribute('allowEmptySearch',     false,    true, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('adm:showControl',     false,    false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('wrapTagCssClass',   false,  NULL,   COMPONENT_TYPE_STRING);
        $this->defineAttribute('removeTitleWithNoFilter',   false,  false,   COMPONENT_TYPE_BOOLEAN);
        parent::init();

        $this->canHaveChilds = false;
    }

    function process()
    {
        if ($this->getAttribute('adm:showControl')) {
            $content = $this->_parent->loadContent($this->getId());
            if (!$content) {
                $this->setAttribute('visible', false);
                return;
            }
        }

        $query                = $this->getAttribute('query');
        $filtersOrParams     = $this->getAttribute('useQueryParams') ? 'params' : 'filters';
        $cssClass             = explode(',', $this->getAttribute('cssClass'));

        // load filters
        $filtersClass = &$this->getAttribute("filters");
        $filters = is_object($filtersClass) ? $filtersClass->getFilters() : array();

        // is the filters exists but isn't visible
        // the componet title will be removed
        if ((!is_object($filtersClass) || !$filtersClass->getAttribute('visible') || !$filtersClass->getAttribute('enabled')) && $this->getAttribute('removeTitleWithNoFilter')) {
            $this->setAttribute('title', '');
        }


        // carica i dati attraverso il componente dataprovider
        $dataProvider     = &$this->getAttribute('dataProvider');
        if (is_null($dataProvider))
        {
            // TODO: fatal error
            // visualizzare errore
        }

        $skipSearch = false;
        if (!$this->getAttribute('allowEmptySearch'))
        {
            $skipSearch = true;
            if (count($filters))
            {
                foreach($filters as $k=>$v)
                {
                    if (!empty($v))
                    {
                        $skipSearch = false;
                        break;
                    }
                }
            }
        }

        // esegue la paginazione
        $paginateClass    = $this->getAttribute("paginate");
        $pageLimits = NULL;
        if ( !$skipSearch && is_object( $paginateClass ) )
        {
            $paginateClass->setRecordsCount();
            $pageLimits = $paginateClass->getLimits();
        }

        $this->_content = new pinax_components_RecordSetListVO();
        $this->_content->title = $this->getAttributeString('title');
        $this->_content->cssClass = $this->getAttribute('wrapTagCssClass');
        $this->_content->pageType = $this->_application->getPageType();
        $this->_content->total = 0;
        if (!$skipSearch)
        {
            $iterator = $this->loadIterator($dataProvider, $query, $filters, $filtersOrParams, $pageLimits);
            $this->_content->records = new pinax_components_RecordSetListIterator( $this->_application, $this, $iterator, $this->routeUrl, $cssClass, $this->getAttribute('processCell'), $this->getAttribute('processCellParams') );
            $this->_content->total = $iterator->count();

            if ( is_object( $paginateClass ) ) {
                $paginateClass->setRecordsCount( $this->_content->total );
            }
        }
    }

    public function addRoute( $mapTo, $routUrl )
    {
        $this->routeUrl[ $mapTo ] = $routUrl;
    }


    public function getContent()
    {

        if ($this->contentFromSkin && count($this->childComponents) && $this->_content->total > 0)
        {
            for ($i=0; $i<count($this->childComponents);$i++)
            {
                $child = $this->childComponents[$i];
                $id = preg_replace('/^(.*#)?('.$this->getId().'\-)?/', '', $child->getId());
                $child->setContent(null);
                $child->process();
                $r = $child->getContent($this->_tagname);
                $this->_content->records->current()->{$id} = $r;
            }
        }

        if (!$this->contentFromSkin) {
            $this->contentFromSkin = true;
        }

        return $this->_content;
    }

    function loadContent($id, $bindTo = '')
    {
        $id = preg_replace('/^(.*#)?/', '', $id);
        return $this->_content->records ? $this->_content->records->current()->{$id} : 0;
    }


    public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );

        $routeUrl = $node->hasAttribute( 'routeUrl' ) ? $node->getAttribute( 'routeUrl' ) : '';
        if ($routeUrl) $compiler->_classSource .= '$n'.$counter.'->addRoute( "__url__", "'.$routeUrl.'" );';
        foreach ($node->childNodes as $n )
        {
            if ( $n->nodeName == "pnx:routeUrl" )
            {
                $mapTo = $n->hasAttribute( 'mapTo' ) ? $n->getAttribute( 'mapTo' ) : '';
                $name = $n->hasAttribute( 'name' ) ? $n->getAttribute( 'name' ) : '';
                if ( $mapTo && $name )
                {
                    $compiler->_classSource .= '$n'.$counter.'->addRoute( "'.$mapTo.'", "'.$name.'" );';
                }
            } else {
                $oldcounter = $counter;
                $compiler->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix );
            }
        }

        return false;
    }


    public static function translateForMode_edit($node) {
        if ($node->hasAttribute('adm:showControl') && $node->getAttribute('adm:showControl') == 'true') {
            $attributes = array();
            $attributes['id'] = $node->getAttribute('id');
            $attributes['label'] = $node->getAttribute('label');
            $attributes['data'] = "type=checkbox";
            $attributes['xmlns:pnx'] = "pinax.components.*";
            return pinax_helpers_Html::renderTag('pnx:Checkbox', $attributes);
        }
    }

    public function getCount()
    {
        return $this->_content->total;
    }

    /**
     *
     * @param object $dataProvider
     * @param string $query
     * @param array $filters
     * @param string $filtersOrParams
     * @param array $pageLimits
     * @return object
     */
    protected function loadIterator($dataProvider, $query, $filters, $filtersOrParams, $pageLimits)
    {
        return $dataProvider->loadQuery( $query,
                        array(    $filtersOrParams     => $filters,
                                'limit'        => $pageLimits,
                                'numRows'     => true)
                        );
    }

}

class pinax_components_RecordSetListVO
{
    var $tile = '';
    var $pageType = '';
    var $records = NULL;
    var $hasFilter = false;
    var $params = NULL;
    var $total = 0;
    var $cssClass = '';
}

class pinax_components_RecordSetListIterator extends PinaxObject implements Iterator, Countable
{
    private $parent;
    private $iterator;
    private $routeUrl;
    private $cssClass;
    private $processCell;
    private $currentAr;
    private $tempCssClass = array();
    private $processCellParams;

    function __construct( $application, $parent, $iterator, $routeUrl, $cssClass, $processCell=null, $processCellParams=null )
    {
        $this->parent = $parent;
        $this->iterator = $iterator;
        $this->routeUrl = $routeUrl;
        $this->cssClass = $cssClass;
        $this->processCellParams = $processCellParams;
        if ($processCell) {
            $this->processCell = pinax_ObjectFactory::createObject($processCell, $application);
        }
    }

    function current()
    {
        if (!$this->currentAr) {
            $this->currentAr = $this->iterator->current();

            call_user_func(array($this->parent, 'getContent'), array());

            // aggiunge propriet?? dinamiche
            if (!count( $this->tempCssClass ) )
            {
                $this->tempCssClass = $this->cssClass;
            }
            $this->currentAr->__cssClass__ = count( $this->tempCssClass ) ? array_shift( $this->tempCssClass ) : '';
            $languageId = $this->parent->_application->getLanguage();
            foreach( $this->routeUrl as $k => $v ) {
                if ($k=='__url__' && $this->currentAr->fieldExists('url') && $this->currentAr->url) {
                    $this->currentAr->$k = $languageId.'/'.$this->currentAr->url;
                } else {
                    $this->currentAr->$k = __Link::makeURL( $v, $this->currentAr->getValuesAsArray() );
                }
            }
            if ($this->processCell) {
                $ar = &$this->currentAr;
                $r = call_user_func_array(array($this->processCell, 'renderCell'), array(&$ar, $this->processCellParams));

                if (!is_null($r)) {
                    $this->currentAr = $r;
                }
            }

        }
        return $this->currentAr;
    }

    function key()
    {
        return $this->iterator->key();
    }

    function next()
    {
        $this->iterator->next();
        $this->currentAr = null;
    }

    function rewind()
    {
        $this->iterator->rewind();
        $this->currentAr = null;
    }

    function valid()
    {
        return $this->iterator->valid();
    }

    function count()
    {
        return $this->iterator->count();
    }
}
