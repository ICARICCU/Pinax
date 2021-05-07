<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_vo_SqlQueryVO
{
    private $sql = '';

    function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function toArray()
    {
        return ['sql' => $this->sql];
    }

}
