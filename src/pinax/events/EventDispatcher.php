<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** class pinax_events_EventDispatcher */
class pinax_events_EventDispatcher
{

	static function addEventListener($type, &$listener, $useCapture=false, $method=null )
	{
		$type = strtolower( $type );
		$eventsChain = &pinax_ObjectValues::get('pinax.events.EventTarget', 'events', array());
		if (!isset($eventsChain[$type]))
		{
			$eventsChain[$type] = array();
		}
		$eventsChain[$type][] = array('listener' => &$listener, 'useCapture' => $useCapture, 'method' => $method);
	}

	static function removeEventListener($type, &$listener, $useCapture=false)
	{
		// TODO
	}

	static function dispatchEvent(&$evt)
	{
		$eventsChain = &pinax_ObjectValues::get('pinax.events.EventTarget', 'events', array());
		if (isset($eventsChain[$evt->type]))
		{
			for($i=0; $i<count($eventsChain[$evt->type]); $i++)
			{
				$listener = $eventsChain[$evt->type][$i];
				$evt->setCurrentTarget($listener['listener']);
				$method = str_replace(array('@', '.'), '_', $evt->type);
				if (method_exists($listener['listener'], $method))
				{
					$listener['listener']->{$method}($evt);
				}
				else if ( !is_null( $listener['method'] ) && method_exists($listener['listener'], $listener['method'] ) )
				{
					$listener['listener']->{ $listener['method'] }($evt);
				}
			}
		}

		// TODO
		return true;
	}

	static function hasEventListener($type)
	{
		// TODO
		return true;
	}

	static function willTrigger($type)
	{
		// TODO
		return true;
	}
}
