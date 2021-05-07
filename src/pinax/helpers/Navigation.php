<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_Navigation extends PinaxObject
{

    /**
     * @param $location
     * @param $params
     */
    public static function gotoUrl($location, $params=null, $hash='')
	{
		if ($params) {
			$location .= (strpos($location, '?')===false ? '?' : '&').http_build_query($params);
		}
		header('Location: '.$location.$hash);
		echo '<html><head><meta http-equiv="refresh" content="1;url='.$location.'"/></head></html>';
		exit;
	}

    /**
     * @return void
     */
	public static function goHere()
	{
        /** @var pinax_application_Application $application */
		$application = &pinax_ObjectValues::get('org.pinax', 'application');
		pinax_helpers_Navigation::gotoUrl( pinax_helpers_Link::makeUrl( 'link', array( 'pageId' => $application->getPageId() ) ) );
	}

    /**
     * Show Access Denied page
     *
     * @param boolean $userIsLogged
     * @return void
     */
    public static function accessDenied($userIsLogged=false)
    {
        __Session::set('pinax.loginUrl', pinax_helpers_Link::scriptUrl());

        if (!$userIsLogged && pinax_Routing::exists('login')) {
            __Session::set('pinax.loginError', __Tp('LOGGER_INSUFFICIENT_GROUP_LEVEL'));
            __Session::set('pinax.loginUrl', pinax_helpers_Link::scriptUrl());
            self::gotoUrl(pinax_Routing::makeUrl('login'));
        } else if (pinax_Routing::exists('accessDenied')) {
            self::gotoUrl(pinax_Routing::makeUrl('accessDenied'));
        } else {
            pinax_Exception::show403(__T('Access is denied'));
        }
    }

    /**
     * @param string $message
     * @return void
     */
    public static function notFound($message='')
    {
        if (pinax_Routing::exists('404')) {
            self::gotoUrl(pinax_Routing::makeUrl('404'));
            exit;
        }

        $message = $message ? : __T('PNX_ERR_404');
        pinax_Exception::show404($message);
    }

}
