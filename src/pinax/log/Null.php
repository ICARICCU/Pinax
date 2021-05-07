<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_log_Null extends pinax_log_LogBase
{

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
		return true;
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
}
