<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_RecordSetBox extends pinax_components_Component
{
	protected $_dataProvider;

	protected $_isEnabled;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('cssClass', 		false,	'even,odd',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('dataProvider',	true, 	NULL,	COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('filters',		false, 	NULL,	COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('numRecord',		false, 	5,		COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('query', 		false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('routeUrl', 		false,	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('title', 		false,	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('getRelations', 	false,	false,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:showControl', 	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('label', 		false,	'',	COMPONENT_TYPE_STRING);

		parent::init();
	}

	function process()
	{
		$this->_isEnabled = $this->getAttribute('adm:showControl') ? $this->_parent->loadContent($this->getId())=="1" : true;
		$this->_content = array();
		$this->_content['records'] = array();

		if ($this->_isEnabled)
		{
			$filters = array();
			if (is_object($this->getAttribute("filters")))
			{
				$filtersClass 	= &$this->getAttribute("filters");
				$filters 		= $filtersClass->getFilters();
			}

			// carica i dati attraverso il componente dataprovider
			$this->_dataProvider = &$this->getAttribute('dataProvider');
			if (!is_null($this->_dataProvider))
			{
				$iterator = &$this->_dataProvider->loadQuery($this->getAttribute('query'), array( 'filters' => $filters ));
				if (!is_null($iterator))
				{
					$this->_content['title'] 	= $this->getAttributeString('title');
					$this->_content['records'] 	= pinax_helpers_ActiveRecord::recordSet2List(	$iterator,
																									$this->getAttribute("routeUrl"),
																									explode(',', $this->getAttribute('cssClass')),
																									$this->getAttribute('numRecord'),
																									array(),
																									$this->getAttribute("getRelations"));
				}
			}
			else
			{
				// TODO: fatal error
				// visualizzare errore
			}
		}
	}
}

class pinax_components_RecordSetBox_render extends pinax_components_render_Render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<span>ERROR: custom skin required<br /></span>
EOD;
		return $skin;
	}
}
