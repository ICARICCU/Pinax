<?php
abstract class pinax_oaipmh_core_AbstractRecordIterator
{
    protected $position = 0;

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }
}
