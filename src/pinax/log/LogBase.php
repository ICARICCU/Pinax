<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
	%a = remote IP
	%A = server IP
	%q = query string
	%l = level name
	%L = level number
	%g = group
	%m = message
	%t = time
*/

if (!defined('PNX_LOG_EVENT')) 		define('PNX_LOG_EVENT', 'logByEvent');
if (!defined('PNX_LOG_DEBUG')) 		define('PNX_LOG_DEBUG', 1);
if (!defined('PNX_LOG_SYSTEM')) 	define('PNX_LOG_SYSTEM', 2);
if (!defined('PNX_LOG_INFO')) 		define('PNX_LOG_INFO', 4);
if (!defined('PNX_LOG_WARNING')) 	define('PNX_LOG_WARNING', 8);
if (!defined('PNX_LOG_ERROR')) 		define('PNX_LOG_ERROR', 16);
if (!defined('PNX_LOG_FATAL')) 		define('PNX_LOG_FATAL', 32);
if (!defined('PNX_LOG_ALL')) 		define('PNX_LOG_ALL', 255);


class pinax_log_LogBase extends PinaxObject
{
	/*private*/ var $_formatFunc 	= NULL;
	/*private*/ var $_logFormat 	= '[%l] %g %T%t %T%m';
	/*private*/ var $_timeFormat 	= 'Y-m-d H:I:S';
	/*private*/ var $_level 		= PNX_LOG_ALL;
	/*private*/ var $_levelsName 	= array(PNX_LOG_DEBUG 	=> 'DEBUG',
											PNX_LOG_SYSTEM 	=> 'SYSTEM',
											PNX_LOG_INFO 	=> 'INFO',
											PNX_LOG_WARNING => 'WARNING',
											PNX_LOG_ERROR 	=> 'ERROR',
											PNX_LOG_FATAL 	=> 'FATAL',
											PNX_LOG_ALL 	=> 'ALL');
	/*private*/ var $_group 		= '';
	/*private*/ var $_isOpen 		= false;

	/**
     * String containing the end-on-line character sequence.
     * @var string
     * @access private
     */
    var $_eol = "\r\n";

	protected $forceMessageToString = true;

	/**
	 * @param string     $msg
	 * @param int        $level
	 * @param string     $group
	 * @param bool|false $addUserInfo
	 *
	 * @return bool
	 * @throws Exception
	 */
	function __construct($options=[], $level=PNX_LOG_DEBUG, $group='')
	{
		$this->_level = $level;
		$this->_group = $group;

		if (isset($options['logFormat']))
		{
			$this->_logFormat = $options['logFormat'];
		}

		if (isset($options['timeFormat']))
		{
			$this->_timeFormat = $options['timeFormat'];
		}

		$this->addEventListener(PNX_LOG_EVENT, $this);
	}

	// NOTA: Valeryia ha aggiunto a tutti i metodi
	// log() l'argomento $addUserInfo
	// ma non va bene perché questo non è presente

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
		return false;
	}


	function debug($msg, $group='')
	{
		$this->log($msg, PNX_LOG_DEBUG, $group);
	}

	function system($msg, $group='')
	{
		$this->log($msg, PNX_LOG_SYSTEM, $group);
	}

	function info($msg, $group='')
	{
		$this->log($msg, PNX_LOG_INFO, $group);
	}

	function warning($msg, $group='')
	{
		$this->log($msg, PNX_LOG_WARNING, $group);
	}

	function error($msg, $group='')
	{
		$this->log($msg, PNX_LOG_ERROR, $group);
	}

	function fatal($msg, $group='')
	{
		$this->log($msg, PNX_LOG_FATAL, $group);
	}

	function logByEvent($evt)
	{
		if (isset($evt->data['command']) && $evt->data['command']=='flush')
		{
			$this->flush();
		}
		if (isset($evt->data['message']))
		{
			$message = $evt->data['message'];
			if ( is_array( $message ) && $this->forceMessageToString)
			{
				$message = var_export($message, true);
			}
			$this->log($message, isset($evt->data['level']) ? $evt->data['level'] : PNX_LOG_DEBUG , isset($evt->data['group']) ? $evt->data['group'] : '');
		}
	}

    function open()
    {
        return true;
    }

    function close()
    {
        return true;
    }

    function flush()
    {
        return true;
    }


	/* getter/setter */
	function setLevel($value)
	{
		$this->_level = $value;
	}

	function getLevel()
	{
		return $this->_level;
	}

	function setGroup($value)
	{
		$this->_group = $value;
	}

	function getGroup()
	{
		return $this->_group;
	}

	/* private methods */
	/*private*/ function _format($msg, $level, $group)
	{
		if (is_null($this->_formatFunc)) $this->_parseLogFormat();
        /** @var Callable $f */
		$f = $this->_formatFunc;
		return $f($this->_levelsName[$level], $level, $group, $msg).$this->_eol;
	}

	/*private*/ function _parseLogFormat()
	{
		$funCode = '$out = \'\';';

		preg_match_all("|%[aAqlLgmdtT]|U", $this->_logFormat, $part1, PREG_SET_ORDER);
		$part2 = preg_split("|%[aAqlLgmdtT]|U", $this->_logFormat, -1, PREG_SPLIT_DELIM_CAPTURE);
		$part2Count = count($part2);
		for($i=0; $i<$part2Count; $i++)
		{
			if ($i>0)
			{
				switch($part1[$i-1][0])
				{
					case ('%a'):
						$funCode .= '$out .= $_SERVER["REMOTE_ADDR"];';
						break;
					case ('%A'):
						$funCode .= '$out .= $_SERVER["SERVER_ADDR"];';
						break;
					case ('%q'):
						$funCode .= '$out .= $_SERVER["QUERY_STRING"];';
						break;
					case ('%l'):
						$funCode .= '$out .= $level;';
						break;
					case ('%L'):
						$funCode .= '$out .= $levelNum;';
						break;
					case ('%g'):
						$funCode .= '$out .= $group;';
						break;
					case ('%m'):
						$funCode .= '$out .= $msg;';
						break;
					case ('%t'):
						if (preg_match('|^{([^\}]*)}(.*)|', $part2[$i], $tf))
						{
							$newTimeFormat = $tf[1];
							$part2[$i] = $tf[2];

						}
						else
						{
							$newTimeFormat = $this->_timeFormat;
						}

						$newTimeFormat = preg_replace('|([aAbBcCdDegGhHIJmMnprRStTuUVWwxXyYzZ])|', '%$1', $newTimeFormat);
						$funCode .= '$out .= strftime(\''.addslashes($newTimeFormat).'\');';

						break;
					case ('%T'):
						$funCode .= '$out .= "\t";';
						break;
				}
			}

			$funCode .= '$out .= \''.addslashes($part2[$i]).'\';';
		}

		$funCode .= 'return $out;';
		$this->_formatFunc = create_function('$level, $levelNum, $group, $msg', $funCode);
	}

	function _check($level, $group)
	{
		return (($level & $this->_level)>=1 && ($this->_group==$group || $this->_group=='*'));
	}
}
