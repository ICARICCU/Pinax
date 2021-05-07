<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PinaxClassLoader {

	/**
	 * @var null|self
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	public $libMap = array();

	/**
	 * @return void
	 */
	public static function register()
	{
		if (!self::$instance) {
			self::$instance = new PinaxClassLoader();
		}
		spl_autoload_register(array(self::$instance, 'loadClass'));
	}

	/**
	 * @return void
	 */
	public static function unregister()
	{
		spl_autoload_unregister(array(self::$instance, 'loadClass'));
	}

	/**
	 * @param string $name
	 * @param string $path
	 *
	 * @return void
	 */
	public static function addLib($name, $path)
	{
		self::$instance->libMap[$name] = $path;
	}

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	public function loadClass($className)
	{
		foreach($this->libMap as $name=>$path) {
			if (strpos($className, $name)===0) {
				$fileName = str_replace(array($name.'\\', '\\'), array('/', '/'), $className).'.php';
				require_once($path.$fileName);
				return true;
			}
		}

		$className = str_replace( '_', '.', $className );
		return pinax_import( $className );
	}
}
