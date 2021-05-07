<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_log_DB extends pinax_log_LogBase
{
	/*private*/ var $_ar;
	/*private*/ var $application;
	/*private*/ var $_append 	= true;
	/*private*/ var $_lock 		= false;
	/*private*/ var $_keepOpen	= true;
	/*private*/ var $_fileResource 		= false;


	function __construct($options=array(), $level=PNX_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);

		$this->_ar = pinax_ObjectFactory::createModel( 'pinax.models.Log' );
		$this->application = pinax_ObjectValues::get('org.pinax', 'application');

		// TODO
		//$this->_ar->enableQueue();
	}

	function __destruct()
	{
		// TODO
		//$this->_ar->executeQueue();
	}

	/**
	 * @param string     $msg
	 * @param int        $level
	 * @param string     $group
	 * @param bool|false $addUserInfo
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function log($msg, $level = PNX_LOG_DEBUG, $group = '', $addUserInfo = false)
	{
		if (!$this->_check($level, $group))
		{
			return false;
		}

		$this->_ar->emptyRecord();
		$this->_ar->log_level = (string)$level;

		$this->_ar->log_date = new pinax_types_DateTime();
		$this->_ar->log_ip = $_SERVER["REMOTE_ADDR"];
		$this->_ar->log_session = session_id();
		$this->_ar->log_group = $group;
		$this->_ar->log_message = $msg;
		$this->_ar->log_FK_user_id = $this->application->getCurrentUser()->id;

		return $this->_ar->save();
	}
}
