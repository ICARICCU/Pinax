<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_authentication_AuthenticationException extends Exception
{
    const EMPTY_LOGINID_OR_PASSWORD = 1;
    const WRONG_LOGINID_OR_PASSWORD = 2;
    const USER_NOT_ACTIVE = 3;
    const ACCESS_NOT_ALLOWED = 4;

    /**
     * @return self
     */
    public static function emptyLoginIdOrPassword()
    {
        return new self('Empty username or password', self::EMPTY_LOGINID_OR_PASSWORD);
    }

    /**
     * @return self
     */
    public static function wrongLoginIdOrPassword()
    {
        return new self('Wrong username or password', self::WRONG_LOGINID_OR_PASSWORD);
    }

    /**
     * @return self
     */
    public static function userNotActive()
    {
        return new self('User not active', self::USER_NOT_ACTIVE);
    }

    /**
     * @return self
     */
    public static function AccessNotAllowed()
    {
        return new self('Access Not Allowed', self::ACCESS_NOT_ALLOWED);
    }
}
