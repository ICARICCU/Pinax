<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_interfaces_Encrypter
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function encrypt($data);

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decrypt($data);
}
