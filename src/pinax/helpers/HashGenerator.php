<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hashids\Hashids;

class pinax_helpers_HashGenerator extends PinaxObject
{
    protected $hashids;

    function __construct()
    {
        $this->hashids = new Hashids(__Config::get('pinax.helpers.Hash.salt'), 0, '0123456789abcdefghijklmnopqrstuvwxyz');
    }

    public function encode($s)
    {
        return $this->hashids->encode($s);
    }

    public function decode($s)
    {
        $v = $this->hashids->decode($s);
        return $v[0];
    }
}
