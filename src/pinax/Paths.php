<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_Paths
{
    /**
	 * @param string $pathApplication
	 * @param string $pathCore
	 *
	 * @return void
	 */
	public static function init($pathApplication='', $pathCore='', $basePath='')
	{
		$pathsArray 								= &pinax_Paths::_getPathsArray();
 		if (count($pathsArray)) {
            return;
        }

        if (empty($pathCore) || $pathCore == '/') {
            $pathCore = './';
        }
        if (substr($pathCore, -1, 1) != '/') {
            $pathCore .= '/';
        }
        if (substr($pathApplication, -1, 1) != '/') {
            $pathApplication .= '/';
        }

		$pathApplicationDepth 						= count(explode('/', $pathApplication))-1;
		$pathsArray['BASE'] 						= $basePath;
		$pathsArray['CORE'] 						= $pathCore;
		$pathsArray['CORE_CLASSES'] 				= $pathCore.'src/';
		// $pathsArray['CORE_LIBS'] 					= $pathCore.'core/libs/';
		// $pathsArray['CACHE'] 						= 'cache/';
		$pathsArray['CACHE'] 						= dirname( $pathApplication ) .'/cache/';
		$pathsArray['ROOT'] 						= dirname( $pathApplication ).'/';

		$pathsArray['CACHE_CODE'] 					= $pathsArray['CACHE'];
		$pathsArray['CACHE_IMAGES'] 				= $pathsArray['CACHE'];
		$pathsArray['CACHE_CSS'] 					= $pathsArray['CACHE'];
        $pathsArray['CACHE_JS'] 					= $pathsArray['CACHE'];
		$pathsArray['APPLICATION'] 					= $pathApplication;
        $pathsArray['APPLICATION_CLASSES']          = $pathApplication.'classes/';
		$pathsArray['APPLICATION_CONFIG'] 			= $pathApplication.'config/';
		$pathsArray['APPLICATION_LIBS'] 			= $pathApplication.'libs/';
		$pathsArray['APPLICATION_MEDIA_ARCHIVE'] 	= $pathApplication.'mediaArchive/';
		$pathsArray['APPLICATION_PAGE_TYPE'] 		= $pathApplication.'pageTypes/';
		$pathsArray['APPLICATION_STARTUP'] 			= $pathApplication.'startup/';
		$pathsArray['APPLICATION_SHUTDOWN'] 		= $pathApplication.'shutdown/';
		$pathsArray['APPLICATION_TEMPLATE'] 		= $pathApplication.'templates/';
		$pathsArray['APPLICATION_STATIC'] 			= $pathsArray['ROOT'].'static/';
		$pathsArray['STATIC_DIR'] 					= 'static/';
		$pathsArray['CORE_STATIC_DIR'] 				= 'static/pinax/core/js/';
        $pathsArray['UPLOAD']                       = rtrim(sys_get_temp_dir(), '/').'/'.md5(realpath($pathsArray['CORE'])).'/';

		$page 										= pathinfo(PNX_SCRIPNAME);
		$dirname									= explode('/', $page['dirname']);

		$page["basename_noext"] 					= isset($page["extension"]) ? substr($page["basename"], 0, strlen($page["basename"])-(strlen($page["extension"])+1)) : $page["basename"];

		$pathsArray['PAGE_FOLDER'] 					= implode('/', array_splice($dirname, count($dirname)-$pathApplicationDepth, $pathApplicationDepth+1));
		$pathsArray['SEARCH_PATH'] 					= [];
        self::addClassSearchPath($pathsArray['APPLICATION_CLASSES']);
        self::addClassSearchPath($pathsArray['CORE_CLASSES']);
	}


	/**
     * @param string $pathCode
     *
     * @return null
     */
    public static function get($pathCode)
	{
		$pathsArray = &pinax_Paths::_getPathsArray();
		return isset($pathsArray[$pathCode]) ? $pathsArray[$pathCode] : NULL;
	}

	/**
	 * @param string $pathCode
	 * @param null|string $fileName
	 *
	 * @return string|void|bool
	 */
	public static function getRealPath($pathCode, $fileName=null)
	{
		$pathsArray = &pinax_Paths::_getPathsArray();
		$path = $pathsArray[$pathCode];
		// TODO verificare che il path richiesto esiste veramente
		if (is_null($fileName)) {
			return realpath($path).'/';
		} else {
			return realpath($path . ($path ? '/' : '') . $fileName);
		}
	}


	/**
     * @param string $pathCode
     * @param string $value
     *
     * @return void
     */
    public static function set($pathCode, $value)
	{
		$pathsArray = &pinax_Paths::_getPathsArray();
		$pathsArray[$pathCode] = $value;
	}

	/**
	 * @param string $pathCode
	 * @param string $path
	 * @param null|string $relativeTo
	 *
	 * @return void
	 */
	public static function add($pathCode, $path, $relativeTo=null)
	{
        // controlla se l'ultimo carattere è uno slash
        if (substr($path, -1, 1) != "/") {
            $path .= "/";
        }
		$pathsArray 			= &pinax_Paths::_getPathsArray();
		$pathsArray[$pathCode]	= is_null($relativeTo) ? $path : $pathsArray[$relativeTo].$path;
	}


	/**
     * @param string $pathCode
     * @return bool
     */
    public static function exists($pathCode)
	{
		$pathsArray 			= &pinax_Paths::_getPathsArray();
		return isset($pathsArray[$pathCode]);
	}

	/**
	 * @return void
	 */
	public static function dump()
	{
		$pathsArray = &pinax_Paths::_getPathsArray();
		var_dump($pathsArray);
	}

	/**
     * @return mixed
     */
    public static function getClassSearchPath()
	{
		$pathsArray 				= &pinax_Paths::_getPathsArray();
		return $pathsArray['SEARCH_PATH'];
	}

	/**
	 * @param string $path
	 *
	 * @return void
	 */
	public static function addClassSearchPath($path)
	{
		$pathsArray 				= &pinax_Paths::_getPathsArray();
		$pathInfo = is_string($path) ? ['path' => $path, 'psr-4' => ''] : $path;
		$pathInfo['path'] = self::realpath($pathInfo['path']);
		$pathsArray['SEARCH_PATH'][] = $pathInfo;
	}

	/**
	 * @return array
	 */
	private static function &_getPathsArray()
	{
		// Array associativo (PATH_CODE=>PATH)
		static $_pathsArray = array();
		return $_pathsArray;
	}

    /**
     * @return void
     */
    public static function destroy()
    {
        $pathsArray = &self::_getPathsArray();
        $pathsArray = array();
	}

	/**
	 * @return string
	 */
	private static function realpath($path)
	{
		return realpath($path).'/';
	}

}
