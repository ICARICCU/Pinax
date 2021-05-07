<?php

class pinaxcms_core_helpers_FulltextCmsFilter
{
    public static function mediaFilter()
    {
        return function($value) {
            $mediaTypes = ['IMAGE', 'OFFICE', 'PDF', 'ARCHIVE', 'FLASH', 'AUDIO', 'VIDEO', 'OTHER'];
            if (in_array($value, $mediaTypes) or preg_match('/^[\w.]+.php/', $value)) {
                return false;
            }
            return true;
        };
    }
}