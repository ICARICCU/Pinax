<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_Locale
{
	/**
	 * @param string $html
	 * @return string
	 */
	public static function replace($html)
	{
	    preg_match_all('/(\{)((i18n:)([^(\'"\})]*))(\})/', $html, $matches, PREG_OFFSET_CAPTURE);
	    if (count($matches[0])) {
	        for ($i=count($matches[0])-1; $i>=0;$i--) {
	            $html = str_replace($matches[0][$i][0], __Tp($matches[4][$i][0]), $html);
	        }
	    }
	    return $html;
	}
}
