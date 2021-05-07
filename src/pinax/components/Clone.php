<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Clone extends pinax_components_Component
{
    private $targetComponent;
    private $output;
    private $transformerClass;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('target', 		true,	NULL,	COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('transformerClass',   false, '',     COMPONENT_TYPE_STRING);
		parent::init();
	}


    public function process()
    {
        $this->targetComponent = $this->target();
        if ($this->targetComponent) {
            $this->createTransformerClass();
            $this->registerOutputFilter();
        }
    }

    /**
     * @return mixed
     */
    public function getContent()
	{
        $result  = '';

        if ($this->targetComponent) {
            $result = $this->targetComponent->getContent();
            if ($this->transformerClass) {
                $result = $this->transformerClass->transformResult($result);
            }
        }
		return $result;
	}

	public function render($outputMode=NULL, $skipChilds=false)
	{
        $this->addOutputCode($this->transformerClass ? $this->transformerClass->transformRender($this->output) : $this->output);
	}

    /**
     * @return pinax_components_Component|null
     */
    private function target()
	{
        $component = null;
		if ( !is_null($this->getAttribute('target')) && $this->getAttribute( 'enabled' ) ) {
			$component = $this->getAttribute('target');
		}

    	return $component;
	}

    private function createTransformerClass()
    {
		$transformerClassName = $this->getAttribute('transformerClass');
        $this->transformerClass = $transformerClassName ? pinax_ObjectFactory::createObject($transformerClassName) : null;
        if ($this->transformerClass) {
            if (!($this->transformerClass instanceof pinax_components_interfaces_CloneTransformer)) {
                throw pinax_exceptions_InterfaceException::notImplemented('pinax.components.interfaces.CloneTransformer', $transformerClassName);
            }
        }
    }

    private function registerOutputFilter()
    {
        $outputFilters = &pinax_ObjectValues::get('org.pinax:components.Component', 'OutputFilter.post');
        if (!isset($outputFilters[$this->targetComponent->_tagname])) {
            $outputFilters[$this->targetComponent->_tagname] = [];
        }
        $outputFilters[$this->targetComponent->_tagname][] = function($value, $mode) {
            $this->output = $value;
        };
    }

	public static function translateForMode_edit($node) {
		return '';
	}
}
