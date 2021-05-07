<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessRepository_EntityIterator implements Iterator, Countable
{
    use pinax_dataAccessRepository_EntityBuilderTrait;

    protected $iterator;
    protected $repository;

    function __construct($iterator, $repository)
	{
        $this->iterator = $iterator;
        $this->repository = $repository;
    }

    public function current()
    {
        $ar = $this->iterator->current();
        return $this->repository->valuesToEntity($ar->getValuesAsArray());
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function count()
    {
        return $this->iterator->count();
    }
}
