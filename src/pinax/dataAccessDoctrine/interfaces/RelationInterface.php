<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_dataAccessDoctrine_interfaces_RelationInterface
{
    public function build($params=array());
    public function preSave();
    public function postSave();
    public function delete();
}
