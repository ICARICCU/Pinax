<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_RelationHasMany extends pinax_dataAccessDoctrine_AbstractRelation
{
    protected $iterator = NULL;
    protected $bindTo = '';
    protected $objectName = '';
    protected $mapField = '';

    function __construct($parent, $options)
    {
        parent::__construct($parent, $options);
        assert(isset($options['field']));
        $this->key = $options['field'];
        $this->destinationKey = $options['destinationField'];
        $this->bindTo = $options['bindTo'];
        $this->mapField = isset($options['mapField']) ? $options['mapField'] : '';
    }

    function build($params=[])
    {
        $id = $this->parent->getFieldValue($this->key);
        if (!$id) {
            $id = '';
        }

        $this->iterator = pinax_ObjectFactory::createModelIterator($this->className)
                            ->setFilters([$this->destinationKey => $id]);
        $values = [];
        foreach($this->iterator as $model) {
            $values[] = $this->mapField ? $model->{$this->mapField} : $model->getId();
        }
        $this->parent->setFieldValue($this->bindTo, $values);
    }

    function postSave()
    {
        $values = $this->parent->{$this->bindTo};
        $values = array_reduce((is_null($values) ? [] : $values), function($carry, $item){
            if (empty($item)) {
                return $carry;
            }

            $carry[] = $item;
            return $carry;
        }, []);

        $model = \pinax_ObjectFactory::createModel($this->className);
        $mapField = $this->mapField ? $this->mapField : $model->getId();

        $currentRelations = [];
        $iterator = pinax_ObjectFactory::createModelIterator($this->className)
                            ->setFilters([$this->destinationKey => $this->parent->{$this->key}]);

        foreach($iterator as $currentModel) {
            if (!in_array($currentModel->{$mapField}, $values)) {
                $currentModel->delete();
            } else {
                $currentRelations[] = $currentModel->{$mapField};
            }
        }

        foreach($values as $id) {
            if (in_array($id, $currentRelations)) {
                continue;
            }
            $model->emptyRecord();
            $model->{$mapField} = $id;
            $model->{$this->destinationKey} = $this->parent->{$this->key};
            $model->save();
        }
    }

    public function delete()
    {
        $model = \pinax_ObjectFactory::createModel($this->className);
        $model->delete([$this->destinationKey => $this->parent->{$this->key}]);
    }
}
