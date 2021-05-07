<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Login extends pinax_components_LoginBox
{
	var $_error = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('accessPageId',		false, 	NULL,						COMPONENT_TYPE_STRING);
		$this->defineAttribute('allowGroups',		false, 	'',							COMPONENT_TYPE_STRING);
		$this->defineAttribute('backend',			false, 	true,						COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('errorLabel',		false, 	__T('PNX_LOGIN_ERROR'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('userField',			false, 	'loginuser', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('passwordField',		false, 	'loginpsw', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('rememberField',		false, 	'loginremember', 			COMPONENT_TYPE_STRING);

		parent::init();
	}

	function render_html()
	{
		if (!is_null($this->_content['errorLabel']))
		{
			$this->addOutputCode($this->_content['errorLabel']);
		}
	}
}
