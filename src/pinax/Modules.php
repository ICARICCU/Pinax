<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_Modules
{
    static $modulesPref;

    /**
     * @return pinax_ModuleVO[]
     */
	public static function &getModules()
	{
		$modules = &pinax_ObjectValues::get('org.pinax', 'modules', array());
		return $modules;
	}

    /**
	 * @return pinax_ModuleVO[]
	 */
	public static function &getModulesSorted()
	{
		$modules = &pinax_ObjectValues::get('org.pinax', 'modules', array());
		uasort($modules, function($a, $b) {
				return strnatcasecmp($a->name, $b->name);
			});
		return $modules;
	}

    /**
     * @param string $id
     * @return pinax_ModuleVO|null
     */
    public static function getModule( $id )
	{
		$modules = &pinax_ObjectValues::get( 'org.pinax', 'modules', array() );
		return isset( $modules[ $id ] ) ? $modules[ $id ] : null;
	}

    /**
	 * @param pinax_ModuleVO $moduleVO
	 *
	 * @return void
	 */
	public static function addModule( pinax_ModuleVO $moduleVO )
	{
		$modules = &pinax_ObjectValues::get( 'org.pinax', 'modules', array() );
		$modulesState = self::getModulesState();
		if ( isset( $modulesState[ $moduleVO->id ] ) && !$modulesState[ $moduleVO->id ] )
		{
			$moduleVO->enabled = false;
		}
		$modules[ $moduleVO->id ] = $moduleVO;

        if ($moduleVO->enabled && $moduleVO->path) {
            __Paths::addClassSearchPath($moduleVO->path);
        }
	}

    /**
     * @return pinax_ModuleVO
     */
	public static function getModuleVO()
	{
		return new pinax_ModuleVO();
	}


    /**
     * @return array|mixed
     */
    public static function getModulesState()
	{
        if (is_null(self::$modulesPref)) {
            $pref = unserialize( pinax_Registry::get( __Config::get( 'BASE_REGISTRY_PATH' ).'/modules', '') );
            self::$modulesPref = $pref ? :[];
        }

		return self::$modulesPref;
	}

    /**
	 * @param $state
	 *
	 * @return void
	 */
	public static function setModulesState( $state )
	{
		pinax_Registry::set( __Config::get( 'BASE_REGISTRY_PATH' ).'/modules', serialize( $state ) );
		pinax_cache_CacheFile::cleanPHP();
	}

	/**
	 * @return void
	 */
	public static function deleteCache()
	{
		pinax_cache_CacheFile::cleanPHP();
	}

	/**
	 * @return void
	 */
	public static function dump()
	{
		$modules = &pinax_ObjectValues::get( 'org.pinax', 'modules', array() );
		var_dump( $modules );
	}

}
