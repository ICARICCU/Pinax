<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_cache_Iterator extends PinaxObject implements Iterator
{
    private $data = NULL;
    private $pos = 0;

    function __construct($data)
    {
        $this->data = $data;
    }

    function rewind()
    {
        $this->pos = 0;
    }

    function valid()
    {
        return count($this->data) > $this->pos;
    }

    function &first()
    {
        $this->rewind();
        return $this->current();
    }

    function key()
    {
        return $this->pos;
    }

    function current()
    {
        return new pinax_dataAccessDoctrine_cache_ActiveRecord($this->data[$this->pos]);
    }

    function next()
    {
        $this->pos++;
    }

    function count()
    {
        return count($this->data);
    }


    function hasMore()
    {
        return count($this->data) > $this->pos;
    }

    function recordPos()
    {
        return $this->pos;
    }

    function getData()
    {
        return $this->data;
    }

    function setData($data)
    {
        $this->pos = 0;
        $this->data = $data;
    }
}
