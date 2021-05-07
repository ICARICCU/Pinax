<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** class pinax_helpers_Array */
class pinax_helpers_Array extends PinaxObject
{
	// the multisort code if based on http://wiki.grusp.it/tips:array_key_multisort?s=multisort by AlberT (http://www.superalbert.it/)

	/**
	 * ordina un array multidimensionale in base ad un campo
	 *
	 * @param $arr, l'array da ordinare
	 * @param $l la "label" che identifica il campo di ordinamento
	 * @param $f la funzione di ordinamento che si vuole applicare, di default si usa strnatcasecmp()
	 * @return  TRUE in caso di successo, FALSE in caso di fallimento.
	 */
	public static function arrayMultisortByLabel($arr, $l, $invert=false, $f='strnatcasecmp')
	{
		if ( $invert ) {
			return usort($arr, function($a, $b) use ($l, $f) {
				return $f($b[$l], $a[$l]);
			});
		} else {
			return usort($arr, function($a, $b) use ($l, $f) {
				return $f($a[$l], $b[$l]);
			});
		}
	}


	/**
	 * ordina un array multidimensionale in base all'indice
	 *
	 * @param $arr, l'array da ordinare
	 * @param $l la "label" che identifica il campo di ordinamento
	 * @param $f la funzione di ordinamento che si vuole applicare, di default si usa strnatcasecmp()
	 * @return  TRUE in caso di successo, FALSE in caso di fallimento.
	 */
	public static function arrayMultisortByIndex($arr, $l , $f='strnatcasecmp') {
	    return usort($arr, function($a, $b) use ($l, $f) {
			return $f($a[$l], $b[$l]);
		});
	}
}
