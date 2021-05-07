<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_rest_core_Application extends pinax_mvc_core_Application
{
    private $initSiteMap = false;

    /**
     * @param boolean $initSiteMap
     */
    public function setInitSiteMap($initSiteMap)
    {
        $this->initSiteMap = $initSiteMap;
    }

	function run()
	{
		$this->log( "Run Rest application", PNX_LOG_SYSTEM );
        $this->runStartup();

        if ($this->initSiteMap || $this->useXmlSiteMap) {
            $this->_initSiteMap();
        }
		$this->_initRequest();

		pinax_require_once_dir(pinax_Paths::getRealPath('APPLICATION_CLASSES'));

		$this->_startProcess();

		if (file_exists(pinax_Paths::get('APPLICATION_SHUTDOWN')))
		{
			// if the shutdown folder is defined all files are included
			pinax_require_once_dir(pinax_Paths::get('APPLICATION_SHUTDOWN'));
		}
	}

	function _startProcess($readPageId=true)
	{
        if ($readPageId) {
            $evt = array('type' => PNX_EVT_BEFORE_CREATE_PAGE);
            $this->dispatchEvent($evt);
        }

		foreach( $this->proxyMap as $k=>$v )
		{
			$v->onRegister();
		}

		$method = __Request::$method ? __Request::$method : 'get';
		$controller = __Request::get( 'controller', '' );
		$status = 200;
		$directOutput = false;
        $result = array();

        if (__Request::exists('__middleware__')) {
            $middlewareObj = pinax_ObjectFactory::createObject(__Request::get('__middleware__'));
            // verify the cache before page rendering
            // this type of cache is available only for Static Page
            if ($middlewareObj) {
                $middlewareObj->beforeProcess($controller, null);
            }
        }


        if ( $method!='options' && $controller )
		{
			$actionClass = $this->container->get($controller, $this);

			if ( is_object( $actionClass ) )
			{
				$reflectionClass = new ReflectionClass( $actionClass );
				$callMethod = '';
				if ( $reflectionClass->hasMethod( "execute_".$method ) )
				{
					$callMethod = "execute_".$method;
				}
				else if ( $reflectionClass->hasMethod( "execute" ) )
				{
					$callMethod = "execute";
				}

				if ( $callMethod )
				{
                    try {
                        $result = pinax_helpers_PhpScript::callMethodWithParams( $actionClass, $callMethod, __Request::getAllAsArray(), true, $this->container);
                        $directOutput = $actionClass->directOutput;
                    } catch (\Exception $e) {
                        $result = [
                            'http-status' => method_exists($e, 'getHttpStatus') ? $e->getHttpStatus() : 500,
                            'description' => $e->getMessage(),
                            'code' => $e->getCode()
                        ];

                        if (method_exists($e, 'getError')) {
                            $result['error'] = $e->getError();
                        }

                        if (__Config::get('DEBUG')) {
                            $result['file'] = $e->getFile();
                            $result['line'] = $e->getLine();
                            $result['trace'] = $e->getTraceAsString();
                        }

                        if ($result['http-status']===500) {
                            $eventInfo = array( 'type' => PNX_EVT_DUMP_EXCEPTION,
                                                'data' => array(
                                                    'level' => PNX_LOG_FATAL,
                                                    'group' => 'PNX_E_500',
                                                    'message' => $result
                                                ));
                            $this->dispatchEvent($eventInfo);
                        }

                    }

					if (is_array($result)) {
	                    if (isset($result['http-status'])) {
	                        $status = $result['http-status'];
	                        unset($result['http-status']);
	                    } else if (isset($result['httpStatus'])) {
                            $status = $result['httpStatus'];
                            unset($result['httpStatus']);
                        }
                        $keys = array_keys($result);
                        if (count($result)==1 && $keys[0]===0) {
                            $result = $result[0];
                        }
                    } else if (is_object($result)) {
                    	if (property_exists($result, 'httpStatus')) {
                    		$status = $result->httpStatus;
                            unset($result->httpStatus);
                    	}
                    }
				}
				else
				{
					$status = 501;
				}
			}
			else
			{
				$status = 404;
			}
		}
		else if ( $method=='options')
		{
			$status = 200;
		}
		else
		{
			$status = 404;
		}

        if ( $result === false )
        {
            $status = 500;
        }

		if ($status === 404) {
            $evt = ['type' => PNX_EVT_DUMP_404];
            $this->dispatchEvent($evt);
		}
		$httpAccept = (strpos(__Request::get('contentType', @$_SERVER['HTTP_ACCEPT']), 'xml')!==false) ? 'xml' : 'json';


		// sent response
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: -1');
		header( $_SERVER['SERVER_PROTOCOL'].' '.$status.' '.pinax_helpers_HttpStatus::getStatusCodeMessage( $status ) );
		$output = '';
		if ( !is_null($result) )
		{
			if ( $httpAccept == 'json' )
			{
				header("Content-Type: application/json; charset=utf-8");
				if (!$directOutput) {
					// @ serve per evitare waring di conversione nel caso ci siano caratteri non utf8
					$output = @json_encode( $result );
				} else {
					$output = $result;
				}
			}
			else
			{
				$charset = pinax_charset();
				header("Content-Type: text/xml; charset=".$charset);
				if ( !is_array( $result ) || !isset( $result['result'] ) )
				{
					$result = array( 'result' => $result );
				}
				$output = $this->createXml( $result );
			}
		}

        if ($middlewareObj) {
            // verify the cache after content rendering
            $middlewareObj->afterRender($output);
		}

		echo $output;
	}

	private function createXml( $data )
	{
		$xml = new XmlWriter();
		$xml->openMemory();
        $xml->startDocument('1.0', pinax_charset());
		$this->createXmlNode($xml, $data);
		return $xml->outputMemory(true);
	}

	private function createXmlNode( XMLWriter $xml, $data )
	{
	    foreach($data as $key => $value){
	    	if ( $key == "_className" || is_null( $value ) ) continue;

	        if( is_string( $key) && is_object( $value ) )
	        {
	            $xml->startElement($key);
	            $this->createXmlNode($xml, $value );
	            $xml->endElement();
	        }
	        else if( is_string( $key) && is_array($value) )
	        {
	        	$arrayKeys = array_keys( $value );
	        	$wrapTag = preg_replace( '/ies$/i', 'y', $key );
	        	$wrapTag = rtrim( $wrapTag, 's' );
	        	foreach( $arrayKeys as $k )
	        	{
	        		if ( is_string( $k ) )
	        		{
	        			$wrapTag = '';
	        			break;
	        		}
	        	}
	            $xml->startElement($key);
				if ( !empty( $wrapTag ) )
				{
		            foreach( $value as $v )
		            {
		            	if (is_string($v)) {
							$xml->writeElement($key, $v);
		            	} else {
				            $xml->startElement( $wrapTag );
                            $tagName = $wrapTag;
                            if (is_object($v)) {
								$className = preg_split('/_|\\\\/', get_class($v));
                                $tagName = strtolower(array_pop($className));
                            }
                            $xml->startElement($tagName);
				            $this->createXmlNode($xml, $v);
				            $xml->endElement();
		            	}
		            }
				}
				else
				{
				    $this->createXmlNode($xml, $value );
				}
	            $xml->endElement();
	        }
	        else if( is_array($value) )
	        {
	            $this->createXmlNode($xml, $value);
	        }
	        else
	        {
                if (strtolower(pinax_charset()) != 'utf-8') {
                    $value = utf8_encode($value);
                }
		        $xml->writeElement($key, $value);
	        }

	    }
	}

	/**
     * @param string $command
     *
     * @return mixed
     */
	public function executeCommand( $command )
	{
		$controller = $this->getContainer()->get($command, $this);
		if (!is_object($controller)) {
			throw new \Exception(sprintf('%s: can\'t create class with * %s', __METHOD__, $command));
		}

		$params = func_get_args();
		array_shift($params);

		return pinax_helpers_PhpScript::callMethodWithParams( $controller,
																	'execute',
																	$params,
																	true,
																	$this->getContainer());

	}
}
