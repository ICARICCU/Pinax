<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_exceptions_ContentException extends Exception
{
    public static function missingMenuId()
    {
        return new self('Missing param "menu id"');
    }
}
