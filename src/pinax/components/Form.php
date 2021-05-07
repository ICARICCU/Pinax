<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Form extends pinax_components_ComponentContainer
{
    var $_command = '';
    private $currentRenderChildId;

    /**
     * Init
     *
     * @return  void
     * @access  public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('addValidationJs',   false,  true,   COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('action',    false,  NULL,   COMPONENT_TYPE_STRING);
        $this->defineAttribute('fieldset',  false,  false,  COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('method',    false,  'post', COMPONENT_TYPE_STRING);
        $this->defineAttribute('onsubmit',  false,  '',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClass',  false, __Config::get('pinax.form.cssClass'),        COMPONENT_TYPE_STRING);
        // $this->defineAttribute('command',    false, '',      COMPONENT_TYPE_STRING);
        $this->defineAttribute('enctype',   false, '',      COMPONENT_TYPE_STRING);
        $this->defineAttribute('removeGetValues', false, true,      COMPONENT_TYPE_STRING );
        $this->defineAttribute('readOnly',          false,  false,  COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('routeUrl',  false,  '',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('dataProvider',  false,  NULL,   COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('autocomplete',  false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('renderChildWithSkin', false, true, COMPONENT_TYPE_BOOLEAN );

        // call the superclass for validate the attributes
        parent::init();
    }

    function process()
    {
        if ( $this->getAttribute( 'readOnly' ) )
        {
            $this->applyReadOnlyToAllChild( $this );
        }

        $dp = $this->getAttribute('dataProvider');
        if ( is_object( $dp ) )
        {
            $it = $dp->loadQuery();
            if ( is_object( $it ) )
            {
                $arC = $it->current();
                if ( is_object( $arC ) )
                {
                    __Request::setFromArray( $arC->getValuesAsArray() );
                }
            }
        }


        $this->_command = pinax_Request::get($this->getId().'_command', NULL);
        $this->processChilds();
    }

    function render($outputMode=NULL, $skipChilds=false)
    {
        if ( $this->getAttribute( 'addValidationJs' ) )
        {
            $this->_application->addValidateJsCode( $this->getId() );
        }

        if (!is_null($this->getAttribute('skin')) && $outputMode=='html') {
            $this->acceptOutput = true;
            if ($this->_content && is_array($this->_content)) {
                $this->_content = (object)$this->_content;
            } else if (!is_object($this->_content)) {
                $this->_content = new StdClass;
            }

            $renderMode =  $this->getAttribute('renderChildWithSkin') ? 'html' : 'form';

            for ($i=0; $i<count($this->childComponents);$i++)
            {
                if (!$this->childComponents[$i]->getAttribute('visible') || !$this->childComponents[$i]->getAttribute('enabled')) {
                    continue;
                }
                $this->currentRenderChildId = $this->childComponents[$i]->getId();
                $this->_content->{$this->currentRenderChildId} = '';

                $this->childComponents[$i]->render($renderMode);
                $this->state = COMPONENT_STATE_RENDER;
                if ($this->checkBreakCycle())
                {
                    $this->state = COMPONENT_STATE_BLOCKED;
                    $this->breakCycle(false);
                    break;
                }
            }
        }

        parent::render($outputMode, $skipChilds);
    }



    function addOutputCode($output, $editableRegion='', $atEnd=false)
    {
        if ($this->acceptOutput)
        {
            $this->_content->{$this->currentRenderChildId} = $output;
        }
        else
        {
            $this->addParentOutputCode($output, $editableRegion, $atEnd);
        }

    }



    function render_html_onStart()
    {
        $attributes                 = array();
        $attributes['id']           = $this->getId();

        $action = $this->getAttribute('action');
        $routeUrl = $this->getAttribute('routeUrl');

        if (!is_null($action)) {
            $attributes['action'] = $action;
        } else if ($routeUrl) {
            $attributes['action'] = __Link::makeUrl($routeUrl);
        } else {
            $removeValues = $this->getAttribute('removeGetValues');
            if ( $removeValues === true || $removeValues == 'true' )
            {
                $attributes['action'] = pinax_Routing::scriptUrl( true );
            }
            else
            {
                $attributes['action'] = pinax_helpers_Link::removeParams( explode( ',', $removeValues ) );
            }
        }
        $attributes['method']       = $this->getAttribute('method');
        $attributes['onsubmit']     = $this->getAttribute('onsubmit');
        $attributes['class']        = $this->getAttribute('cssClass');
        $attributes['enctype']      = $this->getAttribute('enctype');
        if ($this->getAttribute('autocomplete')) {
            $attributes['autocomplete'] = $this->getAttribute('autocomplete');
        }

        $output  = '<form '.$this->_renderAttributes($attributes).'>';
        if ($this->getAttribute('fieldset')) $output .= '<fieldset>';
        // $output .= pinax_helpers_Html::applyItemTemplate('',
        //              pinax_helpers_Html::hidden($this->getId().'_command', $this->getAttribute('command') ),
        //              true );
        $this->addOutputCode( $output );
    }

    function render_html_onEnd()
    {
        $output = '';
        if ($this->getAttribute('fieldset')) $output .= '</fieldset>';
        $output  .= '</form>';
        $this->addOutputCode($output);
    }


    function getJSAction($action)
    {
        return 'this.form.'.$this->getId().'_command.value = \''.$action.'\'';
    }

    function getCommadFieldName()
    {
        return $this->getId().'_command';
    }


  	/**
	 * @param string $id
	 * @param string $bindTo
	 * @return mixed
	 */
    function loadContent($id, $bindTo = '')
    {
        if (empty($bindTo))
        {
            $bindTo = $id;
        }
        return  pinax_Request::get($bindTo, $this->_parent->loadContent($bindTo));
    }

    function applyReadOnlyToAllChild( $node )
    {
        if ($node->canHaveChilds)
        {
            for ($i=0; $i<count($node->childComponents);$i++)
            {
                if ( is_subclass_of( $node->childComponents[$i], 'pinax_components_HtmlFormElement' ) )
                {
                    $node->childComponents[$i]->setAttribute( 'readOnly', true );
                }
                $this->applyReadOnlyToAllChild( $node->childComponents[$i] );
            }
        }
    }

    function getContent()
    {
        return $this->_content;
    }
}
