<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_SearchFilters extends pinax_components_Form
{
    protected $_filters = array();
    /** @var pinax_SessionEx $sessionEx  */
    protected $sessionEx = NULL;

    protected $rememberMode;

    function init()
    {
        $this->defineAttribute('cssClass',  false, __Config::get('pinax.searchFilters.cssClass'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('wrapDiv',     false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('fieldset',     false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rememberValues',     false, true,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rememberMode',     false, 'persistent',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('setRequest',   false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('filterClass',   false, '',     COMPONENT_TYPE_STRING);

        parent::init();
        $this->setAttribute('addValidationJs', false);
    }

    function process()
    {
        $this->sessionEx     = new pinax_SessionEx($this->getId());
        $this->_command        = pinax_Request::get($this->getId().'_command');
        $this->rememberMode = $this->getAttribute( 'rememberMode' ) == 'persistent' ? PNX_SESSION_EX_PERSISTENT : PNX_SESSION_EX_VOLATILE;

        if ($this->_command=='RESET') {
            $this->resetFilters();
        }

        $this->processChilds();
    }

    private function resetFilters()
    {
        $this->sessionEx->removeAll();
        if (strtolower($this->getAttribute('method')) == 'get') {
            pinax_helpers_Navigation::goHere();
        }
    }


    function loadContent($id, $bindTo='')
    {
        if (empty($bindTo))
        {
            $bindTo = $id;
        }

        if ($this->_command=='RESET')
        {
            $this->_filters[$bindTo] = '';
        }
        else
        {
            if ( $this->getAttribute( 'rememberValues') )
            {
                $defValue = !is_null( $this->sessionEx ) ? $this->sessionEx->get($id, '') : '';
                $this->_filters[$bindTo] = pinax_Request::get($id, $defValue );
            }
            else
            {
                $this->_filters[$bindTo] = pinax_Request::get($id, '');
            }
        }

        if ( !is_null( $this->sessionEx ) )
        {
            $this->sessionEx->set($id, $this->_filters[$bindTo], $this->rememberMode);
        }

        if ( $this->getAttribute('setRequest') )
        {
            __Request::set( $id, $this->_filters[$bindTo] );
        }

        return $this->_filters[$bindTo];
    }

    function setFilterValue($name, $value, $originalValue=null)
    {
        $this->_filters[$name] = $value;
        if ( !is_null( $this->sessionEx ) )
        {
            $this->sessionEx->set($name, is_null( $originalValue ) ?  $value : $originalValue, $this->rememberMode );
        }
    }

    function getFilters()
    {
        $filterClassName = $this->getAttribute('filterClass');
        $filterClass = $filterClassName ? pinax_ObjectFactory::createObject($filterClassName) : null;
        if (!$filterClass) {
            $tempFilters = $this->_filters;
            foreach($this->_filters as $k=>$v)
            {
                if (strpos($k, ',')!==false )
                {
                    unset($tempFilters[$k]);

                    if (!empty( $v )) {
                        $fields = explode(',', $k);
                        $tempOR = array();

                        foreach ($fields as $field) {
                            $tempOR[$field] = $v;
                        }
                        $tempFilters['__OR__'] = $tempOR;
                    }
                }
            }
        } else {
            $tempFilters = $filterClass->getFilters($this->_filters);
        }

        return $tempFilters;
    }

    function render_html_onStart()
    {
        if ($this->getAttribute('wrapDiv'))
        {
            $this->addOutputCode('<div'.$this->_renderAttributes(array('class' => $this->getAttribute('cssClass'))).'>');
            $this->setAttribute('cssClass', '');
        }
        parent::render_html_onStart();
        if ($this->getAttribute('fieldset')) $this->addOutputCode('<fieldset>');
    }

    function render_html_onEnd()
    {
        if ($this->getAttribute('fieldset'))
        {
            $this->addOutputCode('</fieldset>');
        }
        parent::render_html_onEnd();
        if ($this->getAttribute('wrapDiv'))
        {
            $this->addOutputCode('</div>');
        }
    }
}
