<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Repeater extends pinax_components_ComponentContainer
{
    protected $repeaterId;
    protected $repeaterIdLen;
    protected $numRecords;
    protected $contentCount;
    protected $newDataFormat;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('label',        false,     NULL,    COMPONENT_TYPE_STRING);
        // $this->defineAttribute('start',     false, NULL,     COMPONENT_TYPE_INTEGER);
        // $this->defineAttribute('count',     false, NULL,     COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('newMode',    false,    false,    COMPONENT_TYPE_BOOLEAN);
        // $this->defineAttribute('routeUrl',         false,    NULL,    COMPONENT_TYPE_STRING);

        $this->defineAttribute('adm:min',     false, NULL,     COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('adm:max',     false, NULL,     COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('adm:collapsable', false, NULL,     COMPONENT_TYPE_INTEGER);

        // call the superclass for validate the attributes
        parent::init();
    }


    function process()
    {
        $this->repeaterId = $this->getId();
        $this->repeaterIdLen = strlen($this->repeaterId);
        $this->_content = $this->_parent->loadContent($this->repeaterId);

    	$this->newDataFormat = is_array($this->_content);

    	if ($this->newDataFormat) {
            $this->_content = $this->filterVisibleItems($this->_content);
    		$this->numRecords = count($this->_content);
    	} else {
    		$child = $this->childComponents[0];
            $childId = $child->getOriginalId();
            $this->numRecords = is_object($this->_content) && property_exists($this->_content, $childId) ? count($this->_content->$childId) : 0;
    	}
    }


    function getContent()
    {
        $result = array();
        for ($i = 0; $i < $this->numRecords; $i++) {
            $this->contentCount = $i;

            $temp = new StdClass;
            for ($j = 0; $j < count($this->childComponents); $j++) {
                $child = $this->childComponents[$j];
                $child->setContent(null);
                $child->process();
                $c = $child->getContent();
                $temp->{$child->getOriginalId()} = $c;
             }
             $result[] = $temp;
        }
        if ($this->getAttribute('newMode')) {
            $tempResult = new StdClass;
            $tempResult->cssClass = $this->getAttribute('cssClass');
            $tempResult->records = $result;
            return $tempResult;
        }
        return $result;
    }

    function loadContent($id, $bindTo='')
    {
        $id = substr($id, $this->repeaterIdLen + 1);
        return $this->newDataFormat ? $this->_content[$this->contentCount]->{$id} : $this->_content->{$id}[$this->contentCount];
    }

    /**
     * @param array $content
     * @return array
     */
    private function filterVisibleItems($content)
    {
        return array_reduce($content, function($carry, $item){
            if (property_exists($item, 'isVisible') && !$item->isVisible) {
                return $carry;
            }
            $carry[] = $item;
            return $carry;
        }, []);
    }

    public static function compileAddPrefix($compiler, &$node, $componentId, $idPrefix)
    {
        return $idPrefix.'\''.$componentId.'-\'.';
    }

    public static function translateForMode_edit($node) {
        $min = $node->hasAttribute('adm:min') ? $node->getAttribute('adm:min') : '0';
        $max = $node->hasAttribute('adm:max') ? $node->getAttribute('adm:max') : '100';
        $collapsable = $node->hasAttribute('adm:collapsable') && $node->getAttribute('adm:collapsable') == 'true' ? 'true' : 'false';
        $verifyRecord = $node->hasAttribute('adm:verifyRecord') && $node->getAttribute('adm:verifyRecord') == 'true' ? 'true' : 'false';

        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['data'] = 'type=repeat;repeatMin='.$min.';repeatMax='.$max.';collapsable='.$collapsable.';verifyrecord='.$verifyRecord;
        $attributes['xmlns:pnx'] = "pinax.components.*";

        if ($node->hasAttribute('adm:data')) {
            $attributes['data'] .= ';'.$node->getAttribute('adm:data');
        }

        return pinax_helpers_Html::renderTag('pnx:Fieldset', $attributes);
    }
}
