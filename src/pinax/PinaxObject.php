<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PinaxObject implements pinax_interfaces_Logger
{
	protected $_className = NULL;

    /**
     *
     */
	function __construct()
	{
	}

    /**
     * @param bool $toLower
     *
     * @return null|string
     */
	protected function getClassName($toLower=true)
	{
		if ($toLower) {
			return strtolower( is_null($this->_className) ? get_class($this) : $this->_className );
		} else {
			return is_null($this->_className) ? get_class($this) : $this->_className;
		}
	}

	// events functions

    /**
	 * @param string $type
	 * @param pinax_interfaces_Logger $listener
	 * @param bool $useCapture
	 * @param null $method
	 *
	 * @return void
	 */
	protected function addEventListener($type, &$listener, $useCapture=false, $method=null )
	{
		pinax_events_EventDispatcher::addEventListener($type, $listener, $useCapture, $method );
	}

    /**
	 * @param string $type
	 * @param pinax_interfaces_Logger $listener
	 * @param bool $useCapture
	 *
	 * @return void
	 */
	protected function removeEventListener($type, &$listener, $useCapture=false)
	{
		pinax_events_EventDispatcher::removeEventListener($type, $listener, $useCapture);
	}

    /**
	 * @param $evt
	 * @param array|pinax_events_Event $evt
	 *
	 * @return void
	 */
	public function dispatchEvent(&$evt)
	{
		if (is_array($evt))
		{
			$evt = &pinax_ObjectFactory::createObject('pinax.events.Event', $this, $evt);
		}
		pinax_events_EventDispatcher::dispatchEvent($evt);
	}

    /**
	 * @param string $type
	 * @param array $evt
	 *
	 * @return void
	 */
	public function dispatchEventByArray($type, $evt)
	{
		$eventInfo = array('type' => $type, 'data' => $evt);
		$evt = &pinax_ObjectFactory::createObject('pinax.events.Event', $this, $eventInfo);
		pinax_events_EventDispatcher::dispatchEvent($evt);
	}

    /**
     * @param string $type
     * @return bool
     */
	protected function hasEventListener($type)
	{
		return pinax_events_EventDispatcher::hasEventListener($type);
	}

    /**
     * @param string $type
     * @return bool
     */
	protected function willTrigger($type)
	{
		return pinax_events_EventDispatcher::willTrigger($type);
	}

	// error methods

    /**
	 * @param string $msg
	 *
	 * @return void
	 */
	protected function triggerError($msg)
	{
		trigger_error($msg);
	}

	/**
	 * @param string $msg
	 * @param int    $level
	 * @param string $group
	 * @param bool   $addUserInfo
	 *
	 * @return void
	 */
	public function log($msg, $level = PNX_LOG_DEBUG, $group = '', $addUserInfo = false)
	{
		if ( $addUserInfo )
		{
            /** @var pinax_application_User $user */
			$user = &pinax_ObjectValues::get('org.pinax', 'user');
            if ( is_string( $msg ) )
            {
                $msg .= "\t" . $user->toString();
            }
            if ( is_array( $msg ) )
            {
                $msg['user'] = $user->toString();
            }
            if ( is_object( $msg ) )
            {
                $msg->user = $user->toString();
            }
		}

		$this->dispatchEventByArray(
			PNX_LOG_EVENT,
			[
				'level'   => $level,
				'group'   => $group,
				'message' => $msg
			]
		);
	}

    /**
	 * @param string $msg
	 * @param string $debugInfo
	 * @param bool $type
	 * @param string $group
	 * @param bool $addUserInfo
	 *
	 * @return void
	 */
	protected function logAndMessage( $msg, $debugInfo = '', $type=false, $group = '', $addUserInfo = false )
	{
        $type = $type===false || $type===true ?  ($type ? PNX_LOG_ERROR : PNX_LOG_DEBUG) : $type;
		if ( class_exists( 'pinax_application_MessageStack' ) )
		{
			pinax_application_MessageStack::add( $msg, $type==PNX_LOG_ERROR ? PNX_MESSAGE_ERROR : PNX_MESSAGE_SUCCESS );
		}
		$this->log( $msg.' '.$debugInfo, $type, $group, $addUserInfo );
	}
}
