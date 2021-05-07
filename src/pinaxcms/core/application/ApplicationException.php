<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_core_application_ApplicationException extends Exception
{
    public static function notDefaultLanguage()
    {
        return new self('Default language not defined');
    }

    public static function noMenuId()
    {
        return new self('No menu id specified');
    }
}
