<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_SessionEx extends PinaxObject
{
	/**
	 * @var string
	 */
	var $_pageId;

	/**
	 * @var string
	 */
	var $_componentId;

	/**
	 * @var string
	 */
	var $_originalComponentId;

	/**
	 * @var array
	 */
	var $_values;

	/**
	 * @var array
	 */
	var $_allValues;

	function __construct($componentId)
	{
		$this->_pageId 		= strtolower(pinax_ObjectValues::get('pinax.application', 'pageId'));
		$this->_originalComponentId = $componentId;
		$this->_componentId = $componentId.'#'.__Request::get( 'action', '' );
		$this->_allValues	= __Session::get(PNX_SESSION_EX_PREFIX, array(), false, true);

		if (!array_key_exists($this->_pageId, $this->_allValues))
		{
			$this->_allValues[$this->_pageId] = array();
		}

		foreach($this->_allValues as $k=>$v)
		{
			if ($k!=$this->_pageId)
			{
				foreach($v as $kk=>$vv)
				{
					if ($vv['type']!=PNX_SESSION_EX_PERSISTENT)
					{
						unset($this->_allValues[$k][$kk]);
					}
				}
			}
		}

		$this->_values = &$this->_allValues[$this->_pageId];
		__Session::set(PNX_SESSION_EX_PREFIX, $this->_allValues);
	}

	/**
	 * @param string $name
	 * @param null|string $defaultValue
	 * @param bool $readFromParams
	 * @param bool $writeDefaultValue
	 *
	 * @return mixed
	 */
	public function get($name, $defaultValue=NULL, $readFromParams=false, $writeDefaultValue=false)
	{
		$keyName = $this->keyName($name);
		$defaultValue = !array_key_exists($keyName, $this->_values) ? $defaultValue : $this->_values[$keyName]['value'];
		$value = $readFromParams ? pinax_Request::get($this->requestKeyName($name), $defaultValue) : $defaultValue;

		if ($writeDefaultValue) {
			$this->set($name, $value, PNX_SESSION_EX_VOLATILE);
		}

		return $value;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @param int $type
	 *
	 * @return void
	 */
	public function set($name, $value, $type=PNX_SESSION_EX_VOLATILE)
	{
		$keyName = $this->keyName($name);

		if (!array_key_exists($keyName, $this->_values))
		{
			$tempValue 				= array();
			$tempValue['value'] 	= $value;
			$tempValue['type'] 		= $type;
			$this->_values[$keyName] 	= $tempValue;
		}
		else
		{
			$this->_values[$keyName]['value']	= $value;
		}

		__Session::set(PNX_SESSION_EX_PREFIX, $this->_allValues);
	}

	/**
	 * @return bool
	 */
	public function exists($name)
	{
		$keyName = $this->keyName($name);
		return isset($this->_values[$keyName]);
	}


	/**
	 * @return void
	 */
	public function remove($name)
	{
		$keyName = $this->keyName($name);
		if (array_key_exists($keyName, $this->_values)) {
			unset($this->_values[$keyName]);
		}
	}

	/**
	 * @return void
	 */
	public function removeAll()
	{
		$this->_values = array();
	}

	/**
	 * @return array
	 */
	public function getAllAsArray()
	{
		$tempValues = array();
		foreach($this->_values as $k=>$v)
		{
			$tempValues[$k] = $v['value'];
		}
		return $tempValues;
	}

	/**
	 * @param array $values
	 * @param int $type
	 * @return void
	 */
	public function setFromArray($values, $type=PNX_SESSION_EX_VOLATILE)
	{
		foreach($values as $k=>$v)
		{
			$this->_values[$k] = array('value' => $v, 'type' => $type);
		}
	}

	/**
	 * @return string
	 */
	public function getSessionId()
	{
		return session_id();
	}


	/**
	 * @return void
	 */
	public function dump()
	{
		var_dump($this->_values);
	}

	/**
	 * @return string
	 */
	private function keyName($name)
	{
		return $this->_componentId.'_'.$name;
	}

	/**
	 * @return string
	 */
	private function requestKeyName($name)
	{
		return $this->_originalComponentId.'_'.$name;
	}
}
