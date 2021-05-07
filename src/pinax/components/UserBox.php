<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_UserBox extends pinax_components_ComponentContainer
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
		$this->defineAttribute('showWelcome',	false, 	true,	COMPONENT_TYPE_BOOLEAN);
		$this->acceptOutput = true;
		parent::init();
	}

	function process()
	{
		$user = &$this->_application->getCurrentUser();
		$this->_content = array();
		$this->_content['id'] = $this->getId();
		$this->_content['cssClass'] = $this->getAttribute('cssClass');
		$this->_content['message'] = '';
		$this->_content['firstName'] = $user->firstName;
		$this->_content['lastName'] = $user->lastName;
		if ( $this->getAttribute('showWelcome') )
		{
			$this->_content['message'] = pinax_locale_Locale::get('LOGGED_MESSAGE', $user->firstName);
		}
		$this->processChilds();
	}
}

if (!class_exists('pinax_components_UserBox_render'))
{
	class pinax_components_UserBox_render extends pinax_components_render_Render
	{
		function getDefaultSkin()
		{
			$skin = <<<EOD
<div class="" tal:attributes="class UserBox/cssClass; id UserBox/id">
<h3 tal:condition="UserBox/message" tal:content="structure UserBox/message" />
<span tal:omit-tag="" tal:content="structure childOutput" />
</div>
EOD;
			return $skin;
		}
	}
}
