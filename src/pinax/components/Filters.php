<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Filters extends pinax_components_ComponentContainer
{
	var $_filters;

    /**
     * @param $result
     */
	function getChildsInfo(&$result)
	{
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$result[] = array(	'id' => $this->childComponents[$i]->getId(),
								'originalId' => $this->childComponents[$i]->getOriginalId(),
								'className' => get_class($this->childComponents[$i]),
								'parent' => $this->getId());
			if (method_exists($this->childComponents[$i], 'getChildsInfo'))
			{
				$this->childComponents[$i]->getChildsInfo($result);
			}
		}
	}

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->_filters = array();
		// legge i valori dai figli
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			if (method_exists($this->childComponents[$i], 'getItem') &&
				$this->childComponents[$i]->getAttribute( 'visible' ) &&
				$this->childComponents[$i]->getAttribute( 'enabled' ) )
			{
				$item = $this->childComponents[$i]->getItem();
				$this->_filters = array_merge($this->_filters, $item);
			}
		}

		$this->processChilds();
	}

	function render($outputMode = NULL, $skipChilds = false)
	{
	}

	function getFilters()
	{
		return $this->_filters;
	}

}
