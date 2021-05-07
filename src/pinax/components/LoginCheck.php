<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_LoginCheck extends pinax_components_Component
{

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('text',			false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('allowGroups',   false,  '',		COMPONENT_TYPE_STRING);

		parent::init();
	}

	function process()
	{
		$user = &$this->_application->getCurrentUser();

		$allowGroups = $this->getAttribute('allowGroups')!='' ? explode(',', $this->getAttribute('allowGroups')) : array();

		if (!$user->isLogged() || !(count($allowGroups) ? in_array($user->groupId, $allowGroups) : true))
		{
			$this->breakCycle();
		}
	}

	function render($outputMode = NULL, $skipChilds = false)
	{
		$user = &$this->_application->getCurrentUser();
		$allowGroups = $this->getAttribute('allowGroups')!='' ? explode(',', $this->getAttribute('allowGroups')) : array();
		if (!$user->isLogged() || !(count($allowGroups) ? in_array($user->groupId, $allowGroups) : true))
		{
			$this->breakCycle();
			$output = '<div'.($this->getAttribute('cssClass')!='' ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>'.$this->getAttribute('text').'</div>';
			$this->addOutputCode($output);
		}
	}
}
