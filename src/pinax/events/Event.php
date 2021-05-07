<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


DEFINE('EVENT_CAPTURING_PHASE', 1);
DEFINE('EVENT_AT_TARGET', 2);
DEFINE('EVENT_BUBBLING_PHASE', 3);

class pinax_events_Event
{
	var $type;
	var $target;
	var $currentTarget;
	var $eventPhase;
	var $bubbles;
	var $cancelable;
	var $timeStamp;
	var $data;

	function __construct(&$target, $params=array())
	{
		$this->target = $target;

		$allowParams = array('type', 'bubbles', 'cancelable', 'data');
		foreach($allowParams as $p)
		{
			if (isset($params[$p]))
			{
				$this->{$p} = $params[$p];
			}
		}

		$this->type = strtolower( $this->type );
	}

	function initEvent($eventTypeArg, $canBubbleArg=false, $cancelableArg=false, $dataArg=NULL)
	{
		$this->type 		= strtolower( $eventTypeArg );
		$this->bubbles 		= $canBubbleArg;
		$this->cancelable 	= $cancelableArg;
		$this->timeStamp 	= time();
		$this->data 		= $dataArg;

	}

	function stopPropagation()
	{
	}

	function preventDefault()
	{
	}

	function setTarget(&$target)
	{
		$this->target = $target;
	}

	function setCurrentTarget(&$target)
	{
		$this->currentTarget = $target;
	}
}
