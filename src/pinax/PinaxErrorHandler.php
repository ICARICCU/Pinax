<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PinaxErrorHandler {

	/**
	 * @var bool
	 */
	private static $isRegistred = false;

	/**
	 * @var null|self
	 */
	private static $instance = null;

	function __construct()
	{
		set_error_handler(array($this, 'onErrorHandler'), E_ALL);
		set_exception_handler(array($this, 'onExceptionHandler'));
		// register_shutdown_function(array($this, 'onShutdownFunction'));
	}

	/**
	 * @return void
	 */
	public static function register()
	{
		if (!self::$instance) {
			self::$instance = new PinaxErrorHandler();
		}
		self::$isRegistred = true;
	}

	/**
	 * @return void
	 */
	public static function unregister()
	{
		self::$isRegistred = false;
		restore_exception_handler();
		restore_error_handler();
	}

	/**
	 * @param string|int $errno
	 * @param string     $errstr
	 * @param string     $errfile
	 * @param string     $errline
	 *
	 * @return void
	 */
	public function onErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!self::$isRegistred) return;
		$errorlevel=error_reporting();
		if ($errorlevel&$errno && !($errno&E_STRICT))
		{
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		}
	}

	/**
	 * @param Exception $exception
	 *
	 * @return void
	 */
	public function onExceptionHandler($exception)
	{
		if (!self::$isRegistred) return;

		$this->sendLog($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
		pinax_Exception::show($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(), '', 500, $exception->getTrace());
		exit;
	}

	/**
	 * @return false|null
	 */
	public function onShutdownFunction() {
		if (!self::$isRegistred) return;
		$error = error_get_last();
		if ($error && $this->isFatal($error['type'])) {
			throw new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
		}

		return false;
	}

	/**
	 * @param int|string $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 *
	 * @return void
	 */
	private function sendLog($errno, $errstr, $errfile, $errline)
	{
		if (class_exists('pinax_Config')) {
	        $errors = array(
	            1 => 'E_ERROR',
	            2 => 'E_WARNING',
	            4 => 'E_PARSE',
	            8 => 'E_NOTICE',
	            16 => 'E_CORE_ERROR',
	            32 => 'E_CORE_WARNING',
	            64 => 'E_COMPILE_ERROR',
	            128 => 'E_COMPILE_WARNING',
	            256 => 'E_USER_ERROR',
	            512 => 'E_USER_WARNING',
	            2047 => 'E_ALL',
	            2048 => 'E_STRICT',
	            4096 => 'E_RECOVERABLE_ERROR'
	        );

	        $e                = array();
	        $e['code']        = isset($errors[$errno]) ? $errors[$errno] : $errors[1];
	        $e['description'] = $errstr;
	        $e['file']       = $errfile;
	        $e['line']       = $errline;
	        $e['stacktrace'] = array_slice(debug_backtrace(), 2);
	        $eventInfo = array( 'type' => PNX_EVT_DUMP_EXCEPTION,
	                            'data' => array(
	                                'level' => PNX_LOG_FATAL,
	                                'group' => 'PNX_E_500',
	                                'message' => $e
	                            ));
	        $evt = pinax_ObjectFactory::createObject( 'pinax.events.Event', null, $eventInfo );
	        pinax_events_EventDispatcher::dispatchEvent( $evt );
	    }
	}

	/**
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE));
    }
}
