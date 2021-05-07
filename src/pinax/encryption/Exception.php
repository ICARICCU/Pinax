<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_encryption_Exception extends Exception
{
    public static function encryptException()
    {
        return new self('Could not encrypt the data');
    }

    public static function decryptException()
    {
        return new self('Could not decrypt the data.');
    }
}
