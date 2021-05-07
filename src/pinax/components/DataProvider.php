<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_DataProvider extends pinax_components_Component
{
    var $_classPath;
    var $_activeRecord;
    var $_recordIterator;
    private $recordsCount;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('recordClassName',    true,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('query',             false,    'All',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('order',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('orderModifier',     false,    'ASC',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('limit',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('filters',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('categories',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('params',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('checkIntegrity',     false,    true,        COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('queryOperator',     false, 'AND',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('showAll',     false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('filterClass',   false, '',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('defaultLanguageIfNotAvailable', false, __Config::get('pinaxcms.content.defaultLanguageIfNotAvailable'), COMPONENT_TYPE_BOOLEAN);

        parent::init();
    }


    /**
     * Process
     *
     * @return    boolean    false if the process is aborted
     * @access    public
     */
    function process()
    {
        $this->_classPath     = $this->getAttribute('recordClassName');
        if (is_null($this->_classPath))
        {
            throw new Exception(sprintf("DataProvider: record class don't found %s", $this->getAttributeString('recordClassName')));
        }
        else
        {
            $this->_recordIterator = &pinax_ObjectFactory::createModelIterator($this->_classPath);
            $this->_activeRecord = &pinax_ObjectFactory::createModel($this->_classPath);

            if ($this->getAttribute('showAll') && method_exists($this->_recordIterator, 'showAll')) {
                $this->_recordIterator->showAll();
            }
        }

        $this->processChilds();
    }


    /**
     * Render
     *
     * @return    void
     * @access    public
     */
    function render($outputMode = NULL, $skipChilds = false)
    {
    }

    function &loadQuery($queryName='', $options=array())
    {
        $options['params'] = array();

        if (is_null($this->_recordIterator)) {
            $this->_recordIterator = &pinax_ObjectFactory::createModelIterator($this->getAttribute('recordClassName'));

            if ($this->getAttribute('showAll') && method_exists($this->_recordIterator, 'showAll')) {
                $this->_recordIterator->showAll();
            }
        }

        if (empty($queryName))
        {
            $queryName = $this->getAttribute('query');
        }

        $order = $this->getAttribute('order');
        if (!empty($order))
        {
            $order = explode(',', $order);
            $orderModifier = $this->getAttribute('orderModifier');
            $options['order'] = array();
            foreach($order as $v) {
                list($field, $dir) = explode(' ', $v);
                $options['order'][$field] = $dir ? :$orderModifier;
            }
        }

		if ($this->getAttribute('useQueryParams') && isset($options['filters'])) {
            if (count($options['filters'])) {
                foreach($options['filters'] as $k=>$v) {
                    $options['params'][$k] = is_array($v) ? $v[1] : $v;
                }
            }
            unset($options['filters']);
        }

        if ($this->getAttribute('limit')) $options['limit'] = explode(',', $this->getAttribute('limit'));
        if ($this->getAttribute('filters')) $options['filters'] = $this->getAttribute('filters');

        $filterClassName = $this->getAttribute('filterClass');
        $filterClass = $filterClassName ? pinax_ObjectFactory::createObject($filterClassName) : null;
        if ($filterClass) {
            if ($filterClass instanceof pinax_components_interfaces_IDataProviderFilter) {
                $options = $filterClass->getFilters($queryName, $options);
            } else {
                throw pinax_exceptions_InterfaceException::notImplemented('pinax.components.interfaces.IDataProviderFilter', $filterClassName);
            }
        }


        // TODO
        // if ($this->getAttribute('categories')) $options['categories'] = $this->getAttribute('categories');
        if ($this->getAttribute('params')) {
            $params = explode(';', $this->getAttribute('params'));

            if (count($params)) {
                $paramValues = [];

                foreach ($params as $singleParam) {
                    list($paramKey, $paramValue) = explode('=', $singleParam);
                    if (!empty($paramKey)) {
                        if ($paramValue === 'true') {
                            $paramValue = true;
                        } elseif ($paramValue === 'false') {
                            $paramValue = false;
                        }
                        $paramValues[$paramKey] = $paramValue;
                    }
                }

                $options['params'] = $paramValues;
            }
        }

        $it = $this->_recordIterator->load($queryName, $options['params']);

        if (!empty($options['filters'])) {
            if ($this->getAttribute('queryOperator') === 'OR') {
                $it->setOrFilters($options['filters']);
            }
            else {
                $it->setFilters($options['filters']);
            }
        }

        if (isset($options['order'])) {
            $it->setOrderBy($options['order']);
        }

        if (isset($options['limit'])) {
            $it->limit($options['limit']);
        }

        if ($this->getAttribute('defaultLanguageIfNotAvailable') and method_exists($it, 'whereLanguageIs')) {
            $it->whereLanguageIs(__ObjectValues::get('org.pinax', 'languageId'), false);
        }

        $this->recordsCount = $it->count();
        return $it;
    }

    function &load($id)
    {
        $queryName = strtolower($this->getAttribute('query'));
        if ($queryName && $queryName!=='all') {
            $it = &pinax_ObjectFactory::createModelIterator($this->getAttribute('recordClassName'));
            $it->load($queryName, array('id' => $id));
            $this->_activeRecord = $it->first();
            $r = !is_null($this->_activeRecord);
        } else {
            $r = $this->_activeRecord->load($id);

            if (!$r
                && __Config::get('ACL_ROLES')
                && !$this->_user->acl('*', 'visible-fe', true, $id)) {
                    pinax_helpers_Navigation::accessDenied();
            }

            if (!$r) {
                if ($this->getAttribute('defaultLanguageIfNotAvailable')) {
                    $languageProxy = __ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
                    $defaultLanguageId = $languageProxy->getDefaultLanguageId();

                    $r = $this->_activeRecord->load($id, 'PUBLISHED', $defaultLanguageId);
                    if (!$r) {
                        pinax_helpers_Navigation::notFound();
                    } else {
                        __ObjectValues::set('org.pinax', 'translationNotAvailable', true);
                    }
                } else {
                    pinax_helpers_Navigation::notFound();
                }
            }
        }


        return $this->getObject();
    }

    /**
     * @return pinax_dataAccessDoctrine_AbstractActiveRecord
     */
    function &getObject()
    {
        return $this->_activeRecord;
    }

    function &getNewObject()
    {
        $ar = &pinax_ObjectFactory::createModel($this->_classPath);
        //$ar->setDefaultQuery( $this->getAttribute('query') );
        return $ar;
    }

    function getRecordClassName()
    {
        return $this->getAttribute('recordClassName');
    }

    function getItems($name, $bindToField=NULL)
    {
        if (!is_null($bindToField))
        {
            $name = $bindToField;
        }

        $result = array();
        $iterator = &$this->loadQuery();
        foreach ($iterator as $ar) {
            $result[] = array('key' => $ar->getId(), 'value' => $ar->$name);
        }

        return $result;
    }

    function getLastSql()
    {
        return $this->_activeRecord->lastSql;
    }

    function getRecordsCount()
    {
        return $this->recordsCount;
    }
}
