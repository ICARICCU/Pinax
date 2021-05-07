<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_JS extends PinaxObject
{
	/**
	 * @param string $name
	 * @param string $subDir
	 * @param string $type
	 * @return string
	 */
	public static function linkCoreJSfile($name, $subDir='', $type='text/javascript')
	{
		$url = self::getUrl(pinax_Paths::get('CORE_STATIC_DIR').$subDir.$name);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	/**
	 * @param string $url
	 * @param string $compress
	 * @param string $type
	 * @return string
	 */
	public static function linkStaticJSfile($name, $compress=NULL, $type='text/javascript')
	{
		$url = self::getUrl(pinax_Paths::get('STATIC_DIR').$name);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	/**
	 * @param string $url
	 * @param string $compress
	 * @param string $type
	 * @return string
	 */
	public static function linkJSfile($url, $compress=NULL, $type='text/javascript')
	{
		$url = self::getUrl($url);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	/**
	 * @param string $code
	 * @param string $type
	 * @return string
	 */
	public static function JScode($code, $type='text/javascript')
	{
		$output = '<script type="'.$type.'">'.PNX_COMPILER_NEWLINE2.'// <![CDATA['.PNX_COMPILER_NEWLINE2.$code.PNX_COMPILER_NEWLINE2.'// ]]>'.PNX_COMPILER_NEWLINE2.'</script>';
		return $output;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	private static function getUrl($url)
	{
		return str_replace('&', '&amp;', $url);

	}
}
