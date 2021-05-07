<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait pinax_middleware_AuthorizationTokenTrait
{
    /**
     * @return string
     */
    private function authorizationToken(): string
    {
        $requestHeaders = getallheaders();

        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (!isset($requestHeaders['Authorization'])) {
            return '';
        }

        $authorization = trim($requestHeaders['Authorization']);
        if (!preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            return '';
        }
        return $matches[1];
    }
}
