<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_log_File extends pinax_log_LogBase
{
	public $_fileName = '';
	public $_append = true;
	public $_lock = false;
	public $_keepOpen = true;

	/**
	 * @var \resource
	 */
	public $_fileResource;


	/**
	 * @param array      $fileName
	 * @param array      $options
	 * @param int|string $level
	 * @param string     $group
	 */
	public function __construct($fileName, $options=array(), $level = PNX_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);

		$this->_fileName = $fileName;
		if (isset($options['append']))
		{
			 $this->_append = $options['append'];
		}
		if (isset($options['lock']))
		{
            $this->_lock = $options['lock'];
        }
		if (isset($options['keepOpen']))
		{
            $this->_lock = $options['keepOpen'];
        }
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		$this->close();
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

        if (!$this->_isOpen && !$this->open()) {
            return false;
        }

        if ( is_array( $msg ) || is_object( $msg ) )
        {
            $msg = json_encode($msg);
        }

		if ($this->_lock)
		{
			flock($this->_fileResource, LOCK_EX);
		}

		$ret = (fwrite($this->_fileResource, $this->_format($msg, $level, $group)) !== false);

		if ($this->_lock)
		{
			flock($this->_fileResource, LOCK_UN);
		}

		if (!$this->_keepOpen)
		{
			$this->close();
		}

		return $ret;
	}

	/**
	 * @return bool
	 */
    public function open()
    {
		$dir = pathinfo($this->_fileName, PATHINFO_DIRNAME);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

        if (!$this->_isOpen)
		{
            $this->_fileResource = fopen($this->_fileName, ($this->_append) ? 'a' : 'w');


        }

		$this->_isOpen = $this->_fileResource !== false;
        return $this->_isOpen;
    }

	/**
	 * @return bool
	 */
    public function close()
    {
        if ($this->_isOpen)
		{
			if (fclose($this->_fileResource)) $this->_fileResource = false;
        }

		$this->_isOpen = $this->_fileResource !== false;
        return (!$this->_isOpen);
    }

	/**
	 * @return bool
	 */
    public function flush()
    {
        return fflush($this->_fileResource);
    }
}
