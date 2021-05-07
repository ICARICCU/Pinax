<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_dataAccessDoctrine_AbstractRelation extends PinaxObject implements pinax_dataAccessDoctrine_interfaces_RelationInterface
{
    protected $key = '';
    protected $destinationKey = '';
    protected $parent;
    protected $className = '';
    protected $record = null;

    function __construct($parent, $options)
    {
        $this->parent = $parent;
        assert(isset($options['className']));
        $this->className = $options['className'];
    }

    public function preSave()
    {
    }

    public function postSave()
    {
    }

    public function delete()
    {
    }
}
