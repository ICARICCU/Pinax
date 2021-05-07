<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_ComponentContainer extends pinax_components_Component
{
	var $_output;
	var $acceptOutput;
	var $overrideEditableRegion;

	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
		$this->canHaveChilds	= true;
		$this->_output 			= array();
		$this->acceptOutput 	= false;
		$this->overrideEditableRegion 	= true;
	}

	/**
     * @param        $output
     * @param string $editableRegion
     * @param boolean   $atEnd
     */
	function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		if ($output) {
			if ($this->acceptOutput)
			{
				if ($this->overrideEditableRegion)
				{
					$editableRegion = $this->getAttribute('editableRegion');
				}
				$this->_output[] = array('editableRegion' => empty($editableRegion) ? $this->getEditableRegion() : $editableRegion, 'code' => $output, 'atEnd' => $atEnd);
			}
			else
			{
				$this->addParentOutputCode($output, $editableRegion, $atEnd);
			}
		}
	}

    /**
     * @param        $output
     * @param string $editableRegion
     * @param boolean   $atEnd
     */
	function addParentOutputCode($output, $editableRegion='', $atEnd=false)
	{
		parent::addOutputCode($output, $editableRegion, $atEnd);
	}

	/**
	 * @param string $id
	 * @param string $bindTo
	 * @return mixed
	 */
	public function loadContent($id, $bindTo='')
	{
		return method_exists($this->_parent, 'loadContent') ? $this->_parent->loadContent($id,  $bindTo) : '';
	}

	/**
	 * @return mixed
	 */
	public function getChildContent()
	{
		$result = array();
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$onlyAdmin = $this->childComponents[$i]->getAttribute( "onlyAdmin" );
			if ( $onlyAdmin === true )
			{
				$result = array_merge( $result, $this->childComponents[$i]->getContent() );
			}
			else
			{
				$result[$this->childComponents[$i]->getOriginalId()] = $this->childComponents[$i]->getContent();
			}
		}
		return $result;
	}

	/**
	 * @param array $content
	 * @return array
	 */
	protected function stripIdFromContent($content)
    {
        $id = $this->getId();
        $result = array();
        foreach ($content as $k => $v) {
            $result[preg_replace('/^'.$id.'-/', '', $k)] = $v;
        }
        return $result;
    }
}
