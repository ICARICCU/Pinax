<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class pinax_dataAccessDoctrine_ActiveRecord extends pinax_dataAccessDoctrine_AbstractActiveRecord
{
    public function getBaseClassName()
    {
        return 'activerecord';
    }

    public function addField(pinax_dataAccessDoctrine_DbField $field )
    {
        $this->fields[$field->name] = $field;

        if ($field->key) {
            if (!$this->primaryKeyName) {
                $this->primaryKeyName = $field->name;
            } else {
                throw pinax_dataAccessDoctrine_ActiveRecordException::primaryKeyAlreadyDefined($this->tableName);
            }
        }
    }

    public function load($id)
    {
        if (empty($id)) {
            $this->emptyRecord();
            return false;
        }

        if (is_null($this->primaryKeyName)) {
            throw pinax_dataAccessDoctrine_ActiveRecordException::primaryKeyNotDefined($this->tableName);
        }

        if ($this->dirty) {
            $this->emptyRecord();
        }

        $qb = $this->createQueryBuilder()
                   ->select('*')
                   ->where($this->primaryKeyName.' = :id')
                   ->setParameter(':id', $id);

        if ($this->siteField) {
            $qb->andWhere($qb->expr()->eq($this->siteField, ':site'))
               ->setParameter(':site', $this->getSiteId());
        }

        $r = $qb->execute()->fetch();

        if ($r) {
            $this->loadFromArray($r);
            $this->buildAllRelations();
            return true;
        } else {
            $this->emptyRecord();
            return false;
        }
    }


    /**
     * @param array|null $values
     * @param bool|false $forceNew
     *
     * @return bool
     * @throws pinax_validators_ValidationException
     */
    public function save($values = null, $forceNew = false)
    {
        if (!is_null($values)) {
            $this->loadFromArray($values, true);
        }

        if ($this->processRelations) {
            $this->buildAllRelations();
            $this->saveAllRelations(true);
        }

        if ( $this->isNew() || $forceNew )
        {
            if (__Config::get('pinax.dataAccess.validate')) {
                $this->validate(null, true);
            }
            $evt = array('type' => PNX_EVT_AR_INSERT_PRE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
            $result = $this->insert($values);
            $evt = array('type' => PNX_EVT_AR_INSERT.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
        }
        else
        {
            if (__Config::get('pinax.dataAccess.validate')) {
                $this->validate(null);
            }
            $evt = array('type' => PNX_EVT_AR_UPDATE_PRE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
            $result = $this->update($values);
            $evt = array('type' => PNX_EVT_AR_UPDATE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
        }

        if ($this->processRelations)  {
            $this->saveAllRelations(false);
        }

        return $result;
    }

    public function delete($id = null)
    {
        if (is_array($id)) {
            $identifier = $id;
        }
        else {
            $identifier = array($this->primaryKeyName => is_null($id) ? $this->getId() : $id);
        }

        $evt = array('type' => PNX_EVT_AR_DELETE.'@'.$this->getClassName(), 'data' => $this);
        $this->dispatchEvent($evt);

        if ($this->processRelations)
        {
            if ($this->isNew()) {
                $this->load($id);
            }
            $this->buildAllRelations();
            $this->deleteAllRelations();
        }

        $this->emptyRecord();

        return $this->connection->delete($this->tableName, $identifier);
    }

    protected function insert($values=NULL)
    {
        $sequenceName = $this->getSequenceName();
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $insertValues = array();
        $types = array();

        // filtra i campi virtuali e la chiave primaria
        foreach ($values as $fieldName => $value) {
            if (isset($this->fields[$fieldName])) {
                $field = $this->fields[$fieldName];
                if (!$field->virtual && (!$field->key || isset($this->modifiedFields[$fieldName]))) {
                    $insertValues[$fieldName] = $value;
                    $types[] = $field->type;
                }
            }
        }

        foreach ($this->fields as $fieldName => $field) {
            if ($field->option==='onInsert') {
                $insertValues[$fieldName] = new pinax_types_DateTime();
                $types[] = $field->type;
            }
        }

        if ($this->siteField && !isset($values[$this->siteField])) {
            $insertValues[$this->siteField] = $this->getSiteId();
            $types[] = $this->fields[$this->siteField]->type;
        }

        if (!empty($insertValues)) {
            $r = $this->connection->insert($this->tableName, $insertValues, $types);
        }

        if ($r != false) {
            $this->modifiedFields = array();
            if (!isset($insertValues[$this->getPrimaryKeyName()])) {
                $this->setId($this->connection->lastInsertId($sequenceName));
            }
            return $this->getId();
        }
        else {
            return false;
        }
    }

    protected function update($values=NULL)
    {
        $identifier = array($this->primaryKeyName => $this->getId());

        if (is_null($values)) {
            $values = array_intersect_key(get_object_vars($this->data), $this->modifiedFields);
        }

        $updateValues = array();
        $types = array();

        foreach ($values as $fieldName => $value) {
            if (isset($this->fields[$fieldName])) {
                $field = $this->fields[$fieldName];
                if (!$field->virtual && isset($this->modifiedFields[$fieldName])) {
                    $updateValues[$fieldName] = $value;
                    $types[] = $field->type;
                }
            }
        }

        if (!empty($updateValues)) {
            foreach ($this->fields as $fieldName => $field) {
                if ($field->option==='onUpdate') {
                    $insertValues[$fieldName] = new pinax_types_DateTime();
                    $types[] = $field->type;
                }
            }
           $this->connection->update($this->tableName, $updateValues, $identifier, $types);
        }

        return $this->getId();
    }

    /**
     * @return pinax_dataAccessDoctrine_RecordIterator
     */
    public function createRecordIterator() {
        return new pinax_dataAccessDoctrine_RecordIterator($this);
    }

    /**
     * @param array $options
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder($options=array())
    {
        $options = array_merge(['addFrom' => true, 'tableAlias' => 't1'], $options);
        $qb = $this->connection->createQueryBuilder();

        if ($options['addFrom']) {
            $qb->from($this->tableName, $options['tableAlias']);
        }

        return $qb;
    }

    protected function loadSequenceName()
    {
        static $sequenceName;
        static $sequenceNameLoaded = false;
        if (!$sequenceNameLoaded) {
            $sequenceNameLoaded = true;
            $sm = new pinax_dataAccessDoctrine_SchemaManager($this->connection);
            $sequenceName = $sm->getSequenceName($this->getTableName());
        }
        $this->sequenceNameLoaded = true;
        $this->setSequenceName($sequenceName);
    }


    /**
     * @param  array  $values
     * @param  boolean $isNew
     * @return boolean
     */
    protected function collectValidateFields($values=null, $isNew=false)
    {
        if (is_null($values)) {
            $values = array();
            foreach ($this->fields as $fieldName => $field) {
                if ($field->isSystemField || $fieldName==$this->siteField) continue;
                $values[$fieldName] = property_exists($this->data, $fieldName) ? $this->data->$fieldName : null;
            }

            if (!$isNew) {
                $values = array_intersect_key(get_object_vars($this->data), $this->modifiedFields);
            }
        }

        return $values;
    }
}
