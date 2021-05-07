<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_dataAccessRepository_AbstractRepository
{
    use pinax_dataAccessRepository_EntityBuilderTrait;

    protected $modelName;
    protected $entityName;
    protected $prefix = '';
    protected $ar;
    protected $it;


    public function __construct()
    {
        $this->ar = __ObjectFactory::createModel($this->modelName);
        $this->it = $this->ar->createRecordIterator();
    }

    /**
     * @param int $id
     *
     * @return null|StdClass
     */
    public function findById($id)
    {
        $this->ar->emptyRecord();
        $r = $this->ar->load($id);
        return $r ? $this->arToEntity() : null;
    }

    /**
     * @param  pinax_dataAccessRepository_EntityInterface $entity
     * @return int
     */
    public function save(pinax_dataAccessRepository_EntityInterface $entity)
    {
        if (!$entity->isValid()) {
            throw new DomainException('Entity is not valid: ' . get_class($entity));
        }

        $this->ar->emptyRecord();
        if (!$entity->isNew()) {
            $this->ar->load($entity->getId());
        }

        foreach($entity as $k => $v) {
            if (is_null($v) && $this->ar->{$this->prefix.$k}) {
                continue;
            }
            $this->ar->{$this->prefix.$k} = $v;
        }

        return $this->ar->save(null, $entity->isNew());
    }

    /**
     * @param  int $id
     * @return int|boolean
     */
    public function delete($id)
    {
        $this->ar->emptyRecord();
        return $this->ar->delete($id);
    }

    public function valuesToEntity($values)
    {
        if ($this->prefix) {
            $values = array_reduce(array_keys($values), function ($carry, $item) use ($values) {
                $carry[preg_replace('/^'.$this->prefix.'/', '', $item)] = $values[$item];
                return $carry;
            }, []);
        }

        return self::createEntity($this->entityName, $values);
    }

    protected function arToEntity()
    {
        return $this->valuesToEntity($this->ar->getValuesAsArray());
    }

}
