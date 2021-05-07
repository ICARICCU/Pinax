<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_Session implements pinax_interfaces_Session
{
    private $prefix;
    private $timeout;
    private $sessionStore;
    private $sessionStorePrefix;

    /**
     * @param string $prefix
     * @param int $timeout
     * @param string|null $sessionStore
     * @param string|null $sessionStorePrefix
     */
    function __construct($prefix, $timeout, $sessionStore=null, $sessionStorePrefix=null)
    {
        $this->prefix = $prefix;
        $this->timeout = $timeout;
        $this->sessionStore = $sessionStore;
        $this->sessionStorePrefix = $sessionStorePrefix;
        $this->start();
    }

	/**
	 * @return void
	 */
	private function start()
	{
		if (!$this->isStarted()) {
            $prefix = $this->prefix;
            $timeout = $this->timeout;
            $sessionStore = $this->sessionStore;
            $storagePrefix = $this->sessionStorePrefix;

            if ($sessionStore) {
                if (!$storagePrefix) {
                    $storagePrefix = 'PHPSESSID';
                }
                $store = pinax_ObjectFactory::createObject($sessionStore, $timeout, $storagePrefix.$this->prefix);
                if (!$store) {
                    throw new Exception('Session Store don\'t exists: '.$sessionStore);
                }
                session_set_save_handler($store);
            }

			if (!isset($_SESSION)) {
                $this->sessionStart();
			}

			if ( isset( $_SESSION[$this->prefix.'sessionLastAction'] ) && time() - $_SESSION[$this->prefix.'sessionLastAction'] > $timeout ) {
				$_SESSION=array();
			}
			$_SESSION[$this->prefix.'sessionStarted'] = true;
			$_SESSION[$this->prefix.'sessionLastAction'] = time();
		}
	}

	/**
	 * @return void
	 */
	public function stop()
	{
		if ($this->isStarted()) {
			$this->set('sessionStarted', false);
			session_write_close();
		}
	}

	/**
	 * @return void
	 */
	public function destroy()
	{
		if ($this->isStarted()) {
            $this->sessionStart();
			$_SESSION=array();
			session_unset();
			session_destroy();
		}
	}


    /**
     * @return bool
     */
    private function isStarted()
	{
		$this->prefix = $this->prefix;
		// if ( isset($_GET['draft']) && $_GET['draft'] != '' && isset($_GET['sespre']) && $_GET['sespre'] != '' )
		// {
		// 	$this->prefix = $_GET['sespre'];
		// 	__Config::set('SESSION_PREFIX', $this->prefix);
		// }
		if (!isset($_SESSION) || !isset($_SESSION[$this->prefix.'sessionStarted']) || $_SESSION[$this->prefix.'sessionStarted']!==true)  return false;
		else return true;
	}


	/**
     * @param string     $key
     * @param mixed $defaultValue
     * @param bool $readFromParams
     * @param bool $writeDefaultValue
     * @return mixed
     */
    public function get($key, $defaultValue=NULL, $readFromParams=false, $writeDefaultValue=false)
	{
		$this->start();
		if (!array_key_exists($this->prefix.$key, $_SESSION))
		{
			$value = ($readFromParams) ? pinax_Request::get($key, $defaultValue) : $defaultValue;
			if ($writeDefaultValue) $this->set($key, $value);
		}
		else
		{
			$value = $_SESSION[$this->prefix.$key];
		}
		return $value;
	}

    /**
     * @param $key string
     * @param $value mixed
     */
    public function set($key, $value)
	{
		$this->start();
		$_SESSION[$this->prefix.$key] = $value;
	}


    /**
     * @param $key string
     * @return bool
     */
    public function exists($key)
	{
		$this->start();
		return isset($_SESSION[$this->prefix.$key]);
	}


    /**
     * @param $key string
     */
    public function remove($key)
	{
		$this->start();
		$key = $this->prefix.$key;
		if (array_key_exists($key, $_SESSION))
		{
			unset($_SESSION[$key]);
		}
	}

	/**
	 * @param string $keyPrefix
	 * @return void
	 */
    public function removeKeysStartingWith($keyPrefix)
    {
		$this->start();
    	$keyPrefix = $this->prefix.$keyPrefix;
        foreach ($_SESSION as $k => $v) {
            if (substr($k, 0, strlen($keyPrefix)) == $keyPrefix) {
		    	unset($_SESSION[$k]);
            }
        }
	}

	/**
	 * @return void
	 */
	public function removeAll()
	{
		$this->destroy();
		$this->start();
	}

	/**
	 * @return array
	 */
    public function getAllAsArray()
	{
		$this->start();
		return $_SESSION;
	}

    /**
     * @param $values array
     */
	public function setFromArray($values)
	{
		$this->start();
		foreach($values as $k=>$v)
		{
			$_SESSION[$this->prefix.$k] = $v;
		}
	}

    /**
     * @return string
     */
    public function getSessionId()
	{
		return session_id();
	}

    /**
     * @return void
     */
    public function dump()
	{
		$this->start();
		var_dump($_SESSION);
	}

	/**
	 * @return void
	 */
    private function sessionStart()
    {
        if (defined('PINAX_TEST')) return;

        $result = @session_start();
        if (!$result){
            session_regenerate_id(true); // replace the Session ID
            session_start(); // restart the session (since previous start failed)
        }
    }
}
