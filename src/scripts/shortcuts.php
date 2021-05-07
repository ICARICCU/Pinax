<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @return mixed
 */
function __T()
{
	$l = new pinax_locale_Locale();
	$args = func_get_args();
	return call_user_func_array(array($l, 'get'), $args);
}

/**
 * @return mixed
 */
function __Tp()
{
	$l = new pinax_locale_Locale();
	$args = func_get_args();
	return call_user_func_array(array($l, 'getPlain'), $args);
}

/**
 * @param bool $state
 * @param int $n
 *
 * @return void
 */
function pinax_DBdebug($state = true, $n = 0)
{
    if ($state) {
        pinax_dataAccessDoctrine_DataAccess::enableLogging($n);
    } else {
        pinax_dataAccessDoctrine_DataAccess::disableLogging($n);
    }
}
