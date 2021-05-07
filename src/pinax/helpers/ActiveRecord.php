<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_ActiveRecord extends PinaxObject
{
	/**
	 * @param pinax_dataAccessDoctrine_AbstractRecordIterator $iterator
	 * @param string $routeUrl
	 * @param array $cssClass
	 * @param integer $maxRecord
	 * @param array $queryVars
	 * @param boolean $getRelationValues
	 * @return array
	 */
	public static function recordSet2List(&$iterator, $routeUrl='', $cssClass=array(), $maxRecord=NULL, $queryVars=array(), $getRelationValues=false)
	{
		$output = array();
		$tempCssClass = $cssClass;
		$i = 0;
		while ($iterator->hasMore())
		{
			$ar = &$iterator->current();
			if ($getRelationValues)
			{
				$ar->setProcessRelations(true);
				$ar->buildAllRelations();
			}
			$values = $ar->getValuesAsArray($getRelationValues, true, true);
			$values = array_merge($values, $queryVars);
			if (!count($tempCssClass)) $tempCssClass = $cssClass;
			if (count($tempCssClass)) $values['__cssClass__'] = array_shift($tempCssClass);

			$values['__url__'] = pinax_helpers_Link::makeURL($routeUrl, $values);
			$output[] = $values;
			$iterator->next();
			$i++;
			if (!is_null($maxRecord) && $i==$maxRecord) break;
		}
		return $output;
	}
}
