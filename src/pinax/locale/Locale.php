<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_locale_Locale extends PinaxObject
{
    private static $debugMode = false;

    /**
     * @return mixed|string
     */
	static function get()
	{
		$args = func_get_args();
		$code = array_shift($args);
        if (self::$debugMode) return $code;

		$values = &pinax_ObjectValues::get('pinax.locale.Locale');
		if (!isset($values[$code]))
		{
			return pinax_encodeOutput( $code );
		}
		if (is_array($values[$code])) return $values[$code];
		if (strpos($values[$code], '<')!==false) return vsprintf($values[$code], $args);

		return pinax_encodeOutput(vsprintf($values[$code], $args));
	}

    /**
     * @return mixed|string
     */
	static function getPlain()
	{
		$args = func_get_args();
		$code = array_shift($args);
        if (self::$debugMode) return $code;

		$values = &pinax_ObjectValues::get('pinax.locale.Locale');
		if (!isset($values[$code]))
		{
			return $code;
		}
		if (is_array($values[$code])) return $values[$code];
		if (strpos($values[$code], '<')!==false) return vsprintf($values[$code], $args);

		return vsprintf($values[$code], $args);
	}

    /**
     * @param array $newValues
     */
	static function append($newValues)
	{
		$values = &pinax_ObjectValues::get('pinax.locale.Locale', '', array());
		$values = array_merge($values, $newValues);
	}

    /**
     * Set the debug mode
     * @param  bool $state
     */
    static function debugMode($state)
    {
        self::$debugMode = $state;
    }
}
