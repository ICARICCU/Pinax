<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_log_ElasticSearch extends pinax_log_LogBase
{
    private $_message = array();
    private $_index = 'pinax';
    private $_type = 'log';
    private $_host = '';

    /**
     * @param array      $options
     * @param int|string $level
     * @param string     $group
     */
    function __construct($options=array(), $level=PNX_LOG_DEBUG, $group='')
    {
        parent::__construct($options, $level, $group);
        if (isset($options['index']))
        {
            $this->_index = $options['index'];
        }
        if (isset($options['type']))
        {
            $this->_type = $options['type'];
        }
        if (isset($options['host'])) {
            $this->_host = $options['host'];
        } else {
            $this->_host = __Config::get ( 'pinax.exception.log.elasticsearch.url' );
        }
        $this->_index = strtolower($this->_index);
        $this->_message['index'] = $this->_index;
        $this->_message['type'] = $this->_type;
    }

    /**
     * @return string
     */
    private function getCallingName() {
        $trace  = debug_backtrace();
        $caller = $trace[5];

        if (isset($caller['class'])) {
            $result = $caller['class'] . '::' . $caller['function'];
        } else {
            $result = $caller['function'];
        }

        return $result;
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

        $t = explode ( " ", microtime ( ) );
        $params = array(
            '@timestamp' => date("Y-m-d\TH:i:s",$t[1]).substr((string)$t[0],1,4).date("P"),
            'host' => !isset( $_SERVER["SERVER_ADDR"] ) ? 'console' : $_SERVER["SERVER_ADDR"],
            'group' => $group,
            'level' => $level,
            'caller' => $this->getCallingName(),
            'pid' => getmypid(),
            'appName' => __Config::get ( 'APP_NAME' )
        );

        $message = array_merge($this->_message, $params);

        if (is_array( $msg ))
        {
            $message = array_merge($message, $msg);
        }
        else
        {
            $message = array_merge($message, array('message' => $msg));
        }

        $data 		= json_encode ( $message );
        $restUrl 	= $this->_host . $this->_index . '-' . date ( "Y.m.d" ) . '/' . $this->_type;
        $ch 		= curl_init ( $restUrl );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen ( $data ) )
        );

        if( ! $result = curl_exec($ch))
        {
            if (class_exists('pinax_Config') && __Config::get('DEBUG') === true) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
        }

        curl_close($ch);

        return $result;
	}
}
