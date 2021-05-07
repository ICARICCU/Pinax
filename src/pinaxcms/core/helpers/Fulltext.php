<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_core_helpers_Fulltext extends PinaxObject
{
    /**
     * @param  object  $data
     * @param  object  $model
     * @param  boolean $setValuesInModel
     * @return string
     */
    public static function make($data, $model, $filterValueFunction = null)
    {
        $minChar = __Config::get('pinaxcms.content.fulltext.minchar');
        $delimiter = __Config::get('pinaxcms.content.fulltext.delimiter');
        if (!$minChar) return '';
        $fulltext = '';

        foreach ($data as $k => $v) {
            // remove the system values
            if ((strpos($k, '__') === 0 && $k != '__title') || !$model->fieldExists($k)) continue;
            self::appendInRefrerence($v, $fulltext, $filterValueFunction);
        }
        return $fulltext;
    }

    /**
     * @param  mixed $value
     * @param  string &$fulltext
     */
    public static function appendInRefrerence($value, &$fulltext, $filterValueFunction = null)
    {
        $minChar = __Config::get('pinaxcms.content.fulltext.minchar');
        $delimiter = __Config::get('pinaxcms.content.fulltext.delimiter');
        if (!$minChar) return;

        if (is_string($value) and strpos($value, '{"')===0) {
            $value = @json_decode($value, true);
        }

        if (is_array($value) || is_object($value)) {
            foreach($value as $v) {
                self::appendInRefrerence($v, $fulltext, $filterValueFunction);
            }
        } elseif (self::isValid($value, $filterValueFunction)) {
            $stripped = trim(html_entity_decode(strip_tags(preg_replace("/<br[ \/]*>/", ' ', $value))));
            $stripped = preg_replace("/[\\s]+/", ' ', $stripped);
            if (!is_numeric($value) && strlen($stripped) > $minChar ) {
                $fulltext .= $stripped.$delimiter;
            }
        }
    }

    private static function isValid($value, $filterValueFunction = null)
    {
        return $filterValueFunction ? $filterValueFunction($value) : true;
    }
}
