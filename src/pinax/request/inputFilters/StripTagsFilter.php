<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_request_inputFilters_StripTagsFilter implements pinax_request_interfaces_IInputFilter
{
    public function filter($values, $excludedFields=null)
    {
        if (!$excludedFields) {
            $excludedFields = [];
        }

        $newValues = [];
        foreach($values as $k=>$v) {
            if (in_array($k, $excludedFields) || !isset($newValues[$k][PNX_REQUEST_VALUE])) {
                $newValues[$k] = $v;
            } else {
                $newValues[$k] = [];
                $newValues[$k][PNX_REQUEST_VALUE] = filter_var($v[PNX_REQUEST_VALUE], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                $newValues[$k][PNX_REQUEST_TYPE] = $v[PNX_REQUEST_TYPE];
            }
        }

        return $newValues;
    }
}

