<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_dataAccessDoctrine_interfaces_IteratorInterface extends Iterator
{
    public function queryGroupForCaching();
    public function querySignatureForCaching();
    public function load($query, $params=null);
    public function first();
    public function count();
}
