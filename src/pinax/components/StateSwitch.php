<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


define('PNX_STATE_SWITCH_SUFFIX', 'state');

class pinax_components_StateSwitch extends pinax_components_ComponentContainer
{
	var $_currentState 	= NULL;
	var $_sessionEx		= NULL;
	var $_oldState		= NULL;
	var $_stateName		= NULL;
	protected $rememberMode;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('customClass',		false, '', 		COMPONENT_TYPE_STRING);
		$this->defineAttribute('defaultState',		false, NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('rememberState',		false, true, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('rememberMode',     false, 'volatile',     COMPONENT_TYPE_STRING);
		$this->defineAttribute('useIdPrefix',		false, false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('overrideEditableRegion',		false, true, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('targetPage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('forceChildCreation',	false, 	NULL,	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->overrideEditableRegion = $this->getAttribute('overrideEditableRegion');
		$this->_stateName 	= $this->getAttribute('useIdPrefix') ? $this->getId().'_'.PNX_STATE_SWITCH_SUFFIX : PNX_STATE_SWITCH_SUFFIX;
		$this->_sessionEx	= pinax_ObjectFactory::createObject('pinax.SessionEx', $this->getId());
		$this->rememberMode = $this->getAttribute( 'rememberMode' ) == 'persistent' ? PNX_SESSION_EX_PERSISTENT : PNX_SESSION_EX_VOLATILE;

		$this->_oldState 	= $this->_sessionEx->get($this->_stateName);

		// se lo stato non è setttato lo cerca nella sessione
		if ($this->getAttribute('rememberState'))
		{
			$this->_currentState = $this->_sessionEx->get($this->_stateName);
		}

		if (is_null($this->_currentState))
		{
			$this->resetState();
		}

		$newState = pinax_Request::get($this->_stateName, NULL);
		if (!empty($newState))
		{
			// cambio di stato
			// TODO
			// verificare che lo stato impostato sia definito
			$this->_currentState = pinax_Request::get($this->_stateName, NULL);
		}

		if ($this->_currentState=='reset')
		{
			$this->_currentState = NULL;
			$this->resetState();
		}
		$this->_currentState = strtolower($this->_currentState);
		$this->_sessionEx->set($this->_stateName, $this->_currentState, $this->rememberMode);

		$customClassName = $this->getAttribute('customClass');
		if (!empty($customClassName))
		{
			$customClass = &pinax_ObjectFactory::createObject($customClassName, $this);
			// TODO
			// createObject purtroppo non passa i parametri in riferimento e questa è una grande limitazione
			$customClass->_parent = &$this;
			if (method_exists($customClass, $this->_currentState))  call_user_func(array($customClass, $this->_currentState), $this->_oldState);
			else if (method_exists($customClass, 'execute_'.$this->_currentState))  call_user_func(array($customClass, 'execute_'.$this->_currentState), $this->_oldState);
		}
		else
		{
			if (method_exists($this, $this->_currentState))  call_user_func(array($this, $this->_currentState), $this->_oldState);
		}

		$this->processChilds();

		if (!empty($customClassName))
		{
			$customClass = &pinax_ObjectFactory::createObject($customClassName, $this);
			$customClass->_parent = &$this;
			if (method_exists($customClass, 'executeLater_'.$this->_currentState))  call_user_func(array($customClass, 'executeLater_'.$this->_currentState), $this->_oldState);
		}
	}

/* */

	function getState()
	{
		return strtolower($this->_currentState);
	}

	function setState($value)
	{
		$this->_currentState = $value;
		$this->_sessionEx->set($this->_stateName, $this->_currentState, $this->rememberMode);
	}

	function resetState()
	{
		// se lo stato non è settato dagli attributi
		if (is_null($this->_currentState)) $this->_currentState = $this->getAttribute('defaultState');

		// se lo stato non è settato lo legge dal primo figlio
		if (is_null($this->_currentState)) $this->_currentState = $this->childComponents[0]->getDefaultState();

		//TODO
		// se lo stato è nullo
		// visualizzare un errore
	}

	// TODO
	// modificare il tipo di implementazione
	// non mi piace che un componente chieda l'url ad un'altro componente
	function changeStateUrl($newState='', $amp=false )
	{
		if ( is_null( $this->getAttribute('targetPage') ) )
		{
			return pinax_helpers_Link::addParams(array($this->_stateName => $newState));
		}
		else
		{
			return pinax_helpers_Link::makeUrl('link', array('pageId' => $this->getAttribute('targetPage')), array($this->_stateName => $newState));
		}
	}

	function refreshToState($newState='', $params=null)
	{
		$url = str_replace( '&amp;', '&', $this->changeStateUrl( $newState ) );
		pinax_helpers_Navigation::gotoUrl( $url, $params);
	}


	function getStateParamName()
	{
		return $this->_stateName;
	}

	// TODO
	// modificare il tipo di implementazione
	// non mi piace che un componente chieda l'url ad un'altro componente
	//
	// TODO
	// la generazione dei link deve sempre passare dal pinax_helpers_Link
	function getJSAction($action, $forceUrl=true)
	{
		$pageId = $this->_application->getPageId();
		$targetPage = $this->getAttribute('targetPage');
		if (!is_null($targetPage))
		{
			$pageId = $this->getAttribute('targetPage');
		}

		if ( $forceUrl )
		{
			$url = '\''.pinax_helpers_Link::makeUrl('link', array('pageId' => $pageId), array($this->_stateName => $action)).'\'';
		}
		else
		{
			$url = '\''.pinax_helpers_Link::addParams( array($this->_stateName => $action) ).'\'';
		}


		return $url;
	}
}
