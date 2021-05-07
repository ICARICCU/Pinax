<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_RelationFactory extends PinaxObject
{
    public static function createRelation(&$parent, $options)
    {
		$relation = NULL;
		switch (strtolower($options['type']))
		{
			case 'hasmany':
			case 'has_many':
				$relation = new  pinax_dataAccessDoctrine_RelationHasMany($parent, $options);
				break;
			case 'jointable':
				$relation = new pinax_dataAccessDoctrine_RelationJoinTable($parent, $options);
				break;
		}

		return $relation;
	}
}
