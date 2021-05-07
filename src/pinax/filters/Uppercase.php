<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_filters_Uppercase extends pinax_filters_OutputFilter
{
	function apply(&$value, &$component)
	{
		if (is_string($value))
		{
			$value = strtoupper($value);
		}
	}
}
