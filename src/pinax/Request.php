<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_Request
{
	/**
	 * @var bool
	 */
	static $decodeUtf8 = false;

	/**
	 * @var bool
	 */
	static $skipDecode = false;

	/**
	 * @var bool
	 */
	static $translateInfo = true;

	/**
	 * @var string
	 */
	static $method = '';

	/**
	 * @var array
	 */
	private static $backupValues;

	/**
	 * @return void
	 */
	public static function init()
	{
		self::$method = strtolower( @$_SERVER['REQUEST_METHOD'] );
		$url = '';
		$params	= &pinax_Request::_getValuesArray(true);
		$charset = strtolower( __Config::get('CHARSET') );
		$requestCharset = @$_SERVER[ 'CONTENT_TYPE' ];
		if ( $charset != "utf-8" && stripos( $requestCharset, 'utf-8' ) !== false )
		{
			self::$decodeUtf8 = true;
		}

		if ( self::$skipDecode )
		{
			self::$decodeUtf8 = false;
		}

		foreach($_GET as $k=>$v)
		{
			if ( self::$decodeUtf8 ) $v = pinax_Request::utf8_decode($v);
			$url .= '&'.$k.'='.$v;
			$params[$k]= array($v, PNX_REQUEST_GET);
		}

		foreach($_POST as $k=>$v)
		{
			if ( self::$decodeUtf8 ) $v = pinax_Request::utf8_decode($v);
			$url .= '&'.$k.'='.$v;
			$params[$k]= array($v, PNX_REQUEST_POST);
		}

		$contentType = self::getContentType();
		$body = @file_get_contents('php://input');
		if ( $body && $contentType && $contentType != 'application/x-www-form-urlencoded' )
		{
			$params['__postBody__'] = array($body, PNX_REQUEST_POST);
			if ('application/json'===$contentType) {
				$output = @json_decode($body);
			} else {
				parse_str( $body, $output );
			}
			if ($output && (is_object($output) || is_array($output))) {
	 			foreach($output as $k=>$v)
				{
					if ( !isset( $params[ $k ] ) )
					{
						if (is_string($v)) $url .= '&'.$k.'='.$v;
						$params[$k]= array($v, PNX_REQUEST_POST);
					}
				}
			}
		}

		$params[ '__url__' ] = array( __Routing::$requestUrl, PNX_REQUEST_GET );
		$params[ '__back__url__' ] = array( $url, PNX_REQUEST_GET );

		if ( self::$translateInfo && isset($params[ 'pageId' ]))
		{
			$pageId = strtolower( $params[ 'pageId' ][ PNX_REQUEST_VALUE ] );
			$translateInfo = __Session::get( '__translateInfo_'.$pageId, array( ) );

			foreach( $translateInfo as $v )
			{
				if ( isset($params[ $v[ 'target_name' ] ]) && $params[ $v[ 'target_name' ] ][ PNX_REQUEST_VALUE ] == $v[ 'label' ] )
				{
					$params[ $v[ 'target' ] ][ PNX_REQUEST_VALUE ] =  $v[ 'value' ];
					$params[ $v[ 'target' ] ][ PNX_REQUEST_TYPE ] =  PNX_REQUEST_POST;
				}
			}
			__Session::remove( '__translateInfo_'.$pageId );
		}

		$values = __Session::get( '__valuesForNextRefresh' );
		if ( isset( $values ) && is_array( $values ) )
		{
			foreach( $values as $k => $v )
			{
				$params[ $k  ][ PNX_REQUEST_VALUE ] = $v;
			}
			__Session::remove( '__valuesForNextRefresh' );
		}

		self::parseBasicAuth();
		self::$backupValues = array_merge($params, array());

		// controlla se c'è da applicare un filtro
		$inputFilter = __Config::get('pinax.request.inputFilter');
		if ($inputFilter) {
			self::applyInputFilter($inputFilter);
		}
	}

	/**
	 * @param string $name
	 * @param string $defaultValue
	 * @param integer $type
	 * @return mixed
	 */
    public static function get($name, $defaultValue=NULL, $type=PNX_REQUEST_ALL)
	{
		$params	= &pinax_Request::_getValuesArray();
		$value = array_key_exists($name, $params) ? $params[$name] : NULL;
		$value = is_null($value) ? $defaultValue : ($type==PNX_REQUEST_ALL || $type==$value[PNX_REQUEST_TYPE] ? $value[PNX_REQUEST_VALUE] : $defaultValue);
		return $value;
	}

	/**
	 * @return mixed
	 */
	public static function getParams()
	{
		$params	= &pinax_Request::_getValuesArray();
		return isset($params[ '__params__' ]) ? $params[ '__params__' ] : array();
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param integer $type
	 * @return void
	 */
    public static function set($name, $value, $type=PNX_REQUEST_ALL)
	{
		$params	= &pinax_Request::_getValuesArray();

		$params[$name] = array($value, $type);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param integer $type
	 * @return void
	 */
	public static function add($name, $value, $type=PNX_REQUEST_ALL)
	{
		$params	= &pinax_Request::_getValuesArray();
		if (isset($params[$name]))
		{
			trigger_error('The param is already set');
		}

		$params[$name] = array($value, $type);
	}

	/**
	 * @param string $key
	 * @param integer $type
	 * @return boolean
	 */
	public static function exists($key, $type=null)
	{
		$params	= &pinax_Request::_getValuesArray();
		if (is_null($type) || $type==PNX_REQUEST_ALL) {
			return isset($params[$key]);
		} else {
			return isset($params[$key]) && $params[$key][PNX_REQUEST_TYPE] == $type;
		}
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 */
	public static function isEqual($name, $value)
	{
		return strtolower( pinax_Request::get( $name, '' ) ) == strtolower( $value );
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public static function remove($name)
	{
		$params	= &pinax_Request::_getValuesArray();
		if (isset($params[$name]))
		{
			unset($params[$name]);
		}
	}

	/**
	 * @return void
	 */
	public static function removeAll()
	{
		$params	= &pinax_Request::_getValuesArray();
		$params = array();
	}

	/**
	 * @return array
	 */
	public static function getAllAsArray()
	{
		$params	= &pinax_Request::_getValuesArray();
		$result = array();
		foreach($params	as $k=>$v)
		{
			$result[$k] = $v[PNX_REQUEST_VALUE];
		}
		return $result;
	}

	/**
	 * Return the request Content Type
	 * @return String
	 */
	public static function getContentType()
	{
		$headerContentType = isset($_SERVER[ 'CONTENT_TYPE' ]) ? $_SERVER[ 'CONTENT_TYPE' ] : '';
		list($contentType) = explode(';', $headerContentType);
	    return strtolower(trim($contentType));
	}


	/**
	 * Return the post body
	 * @return String
	 */
	public static function getBody()
	{
	    return self::get('__postBody__', null);
	}

	/**
	 * @param array $values
	 * @param integer $type
	 * @return void
	 */
	public static function setFromArray($values, $type=PNX_REQUEST_ALL)
	{
		$params	= &pinax_Request::_getValuesArray( true );
		foreach($values as $k=>$v)
		{
			// $params[$k] = is_array($v) ? $v : array($v, $type);
			$params[$k] = array($v, $type);
		}

	}

	/**
	 * @param string $values
	 * @return void
	 */
	public static function setValuesForNextRefresh( $values )
	{
		__Session::set( '__valuesForNextRefresh', $values );
	}

	/**
	 * Apply the input filter
	 *
	 * @param string  $filterName        Filter class path
	 * @param array  $excludedFields    Fields to exclude
	 * @param bool $restoreFromBackup Restore from backup
	 *
	 * @return void
	 */
	public static function applyInputFilter($filterName, $excludedFields=null, $restoreFromBackup=false)
	{
		$filterClass = pinax_ObjectFactory::createObject($filterName);
		if (!$filterClass) {
            throw pinax_exceptions_GlobalException::classNotExists($filterName);
        } else if (!$filterClass instanceof pinax_request_interfaces_IInputFilter) {
            throw pinax_exceptions_InterfaceException::notImplemented('pinax.request.interfaces.IInputFilter', $filterName);
        }

		$params = !$restoreFromBackup ? pinax_Request::_getValuesArray() : self::$backupValues;
        $newParams = $filterClass->filter($params, $excludedFields);
        self::setFromArray($newParams);
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public static function dump($name=null)
	{
	    if ($name) {
    		var_dump(pinax_Request::get($name));
	    } else {
    		$params	= &pinax_Request::_getValuesArray();
    		var_dump($params);
	    }
	}

	/**
	 * @param boolean $init
	 * @return array
	 */
	private static function &_getValuesArray($init=false)
	{
		static $_valuesArray = array();
		if (!count($_valuesArray) && !$init)
		{
			pinax_Request::init();
		}
		return $_valuesArray;
	}

	/**
	 * @param mixed $values
	 * @return mixed
	 */
	private static function utf8_decode($values)
	{
		$output = null;
		if (is_array($values))
		{
			$keys = array_keys($values);
			$count = count($values);
			for ($i = 0; $i < $count; $i++)
			{
				if (is_array($values[$keys[$i]]))
				{
					$values[$keys[$i]] = pinax_Request::utf8_decode($values[$keys[$i]]);
				}
				else
				{
					if ( function_exists('iconv') )
					{
						$output[$keys[$i]] = iconv("UTF-8", "CP1252", $values[$keys[$i]]);
					}
					else
					{
						$output[$keys[$i]] = utf8_decode($values[$keys[$i]]);
					}
				}
			}
			return $values;
		}
		else
		{
			if( function_exists('iconv') )
			{
				return iconv( "UTF-8", "CP1252", $values );
			}
			else
			{
				return utf8_decode( $values );
			}
		}
	}

	/**
	 * @return void
	 */
    public static function destroy()
    {
        $valuesArray = &self::_getValuesArray();
        $valuesArray = array();
    }

	/**
	 * @return string
	 */
	public static function getMethod()
	{
		return strtoupper(self::$method);
	}

	/**
	 * @return string
	 */
	public static function getUser()
	{
		return self::get('PHP_AUTH_USER');
	}

	/**
	 * @return string
	 */
	public static function getPassword()
	{
		return self::get('PHP_AUTH_PW');
	}

	/**
	 * @return void
	 */
	private static function parseBasicAuth()
	{
		$httpAuth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : '');

		if ($httpAuth && preg_match('/Basic\s+(.*)$/i', $httpAuth, $matches)) {
            list($user, $password) = explode(':', base64_decode($matches[1]));
            $_SERVER['PHP_AUTH_USER'] = $user;
            $_SERVER['PHP_AUTH_PW'] = $password;
        }

		self::set('PHP_AUTH_USER', @$_SERVER['PHP_AUTH_USER'], PNX_REQUEST_AUTH);
		self::set('PHP_AUTH_PW', @$_SERVER['PHP_AUTH_PW'], PNX_REQUEST_AUTH);
	}
}
