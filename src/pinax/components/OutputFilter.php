<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_OutputFilter extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('tag',					false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('filter', 				true, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('mode', 					false,	'PRE', 	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
		$this->doLater($this, '_setOutputFilter');
	}

	function _setOutputFilter()
	{
		$filterName = $this->getAttribute('filter');
		$tag = $this->getAttribute('tag');
		// risovle il nome della classe
		if (file_exists(pinax_Paths::get('CORE_CLASSES').'org/pinax/filters/'.$filterName.'.php'))
		{
			$filterName = 'pinax.filters.'.$filterName;
			pinax_import($filterName);
		}
		else
		{
			pinax_import($filterName);
		}

		$className = str_replace('.', '_', $filterName);
		if (class_exists($className))
		{
			// aggiunge il filtro per essere processato
			if ($this->getAttribute('mode')=='PRE')
			{
				$outputFilters = &pinax_ObjectValues::get('org.pinax:components.Component', 'OutputFilter.pre');
			}
			else
			{
				$outputFilters = &pinax_ObjectValues::get('org.pinax:components.Component', 'OutputFilter.post');
			}
			if (!isset($outputFilters[$tag])) $outputFilters[$tag] = array();
			$outputFilters[$tag][] = $className;
		}
	}
}
