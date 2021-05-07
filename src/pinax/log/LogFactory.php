<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_log_LogFactory
{
    /**
     * @return pinax_log_LogBase
     */
    static function &create()
	{
		$args = func_get_args();
		$name = array_shift($args);
		$newObj = NULL;

		if (file_exists(dirname(__FILE__).'/'.$name.'.php'))
		{
			pinax_import('pinax.log.'.$name);
			$className = str_replace('.', '_', 'pinax.log.'.$name);
			$costructString = '$newObj = new '.$className.'(';
			for ($i=0; $i<count($args); $i++)
			{
				$costructString .= '$args['.$i.']';
				if ($i<count($args)-1) $costructString .= ', ';
			}
			$costructString .= ');';
			eval($costructString);
		}
		return $newObj;
	}
}
