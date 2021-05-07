<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_CSS extends PinaxObject
{
	/**
	 * @param string $name
	 * @param string $subDir
	 * @return string
	 */
	public static function linkCoreCSSfile($name, $subDir='')
	{
		$output = '<link rel="stylesheet" type="text/css" media="all" href="'.pinax_Paths::get('CORE_STATIC_DIR').'assets/css/'.$subDir.$name.'" />';
		return $output;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function linkCoreCSSfile2($name)
	{
		$output = '<link rel="stylesheet" type="text/css" media="all" href="'.pinax_Paths::get('CORE_STATIC_DIR').$name.'" />';
		return $output;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function linkStaticCSSfile($name)
	{
		$output = '<link rel="stylesheet" type="text/css" media="all" href="'.pinax_Paths::get('STATIC_DIR').$name.'" />';
		return $output;
	}

	/**
	 * @param string $name
	 * @param string $media
	 * @return string
	 */
	public static function linkCSSfile($name, $media='all')
	{
		if ($media)  {
			$mediaAttr = 'media="'.$media.'"';
		}
		$output = '<link rel="stylesheet" type="text/css" '.$mediaAttr.' href="'.$name.'" />';
		return $output;
	}

	/**
	 * @param string $code
	 * @return string
	 */
    public static function CSScode($code)
    {
		$output = '<style type="text/css">'.$code.'</style>';
		return $output;
	}
}
