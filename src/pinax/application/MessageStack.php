<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define( 'PNX_MESSAGE_SUCCESS', 'SUCCESS' );
define( 'PNX_MESSAGE_FAULT', 'FAULT' );
define( 'PNX_MESSAGE_ERROR', 'ERROR' );

class pinax_application_MessageStack
{
	/**
	 * @param string $message
	 * @param string $type
	 * @return void
	 */
	public static function add($message, $type=PNX_MESSAGE_SUCCESS)
	{
		$messages = &__Session::get('pinax.application.MessageStack',  array());
		if (!isset($messages[$type]))
		{
			$messages[$type] = array();
		}
		$messages[$type][] = $message;
		__Session::set('pinax.application.MessageStack',  $messages );
	}

	/**
	 * @param string $type
	 * @return string[]
	 */
	public static function get($type=NULL)
	{
		$messages = &__Session::get('pinax.application.MessageStack',  array());
		if (is_null($type) || $type=='ALL')
		{
			$tempMessages = array();
			foreach( $messages as $k=>$v )
			{
				$tempMessages = array_merge( $tempMessages, $v );
			}
			return $tempMessages;
		}
		else
		{
			return isset($messages[$type]) ? $messages[$type] : array();
		}
	}

	/**
	 * @return void
	 */
	public static function reset($type=NULL)
	{
		$messages = &__Session::get('pinax.application.MessageStack',  array());
		if (is_null($type) || $type=='ALL')
		{
			$messages = array();
		}
		else
		{
			$messages[$type] = array();
		}
		__Session::set('pinax.application.MessageStack',  $messages );
	}
}
