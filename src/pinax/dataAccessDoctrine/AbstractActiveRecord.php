<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

abstract class pinax_dataAccessDoctrine_AbstractActiveRecord extends PinaxObject
{
    protected $connection;
    protected $tableName;
    protected $tablePrefix;
    protected $sequenceName = null;
    protected $sequenceNameLoaded = false;
    protected $primaryKeyName;
    protected $fields = array();
    protected $modifiedFields = array();
    protected $data;
    protected $virtualData;
    protected $siteField = null;
    protected $relations = array();
    protected $processRelations = false;
    protected $relationBuilded = false;
    protected $driverName;
    protected $baseclassName;
    protected $dirty = false;

    function __construct($connectionNumber=0)
    {
        $this->connection = pinax_dataAccessDoctrine_DataAccess::getConnection($connectionNumber);
        $this->data = new StdClass();
        $this->virtualData = new StdClass();
        $this->driverName = __Config::get( 'DB_TYPE'.($connectionNumber == 0 ? '' : '#'.$connectionNumber) );
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getDriverName()
    {
        return $this->driverName;
    }

    abstract public function getBaseClassName();

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getTableNameWithoutPrefix()
    {
        return substr( $this->getTableName(), strlen( $this->tablePrefix ) );
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }


    public function setTableName($tableName, $prefix="")
    {
        $this->tablePrefix = $prefix;
        $this->tableName = $this->tablePrefix.$tableName;
    }

    public function setSequenceName($sequenceName)
    {
        $this->sequenceName = $sequenceName;
    }

    public function getSequenceName()
    {
        if (!$this->sequenceNameLoaded) {
            $this->loadSequenceName();
        }

        return $this->sequenceName;
    }

    public function getProcessRelations()
    {
        return $this->processRelations;
    }

    public function setProcessRelations($value)
    {
        $this->processRelations = $value;
    }

    abstract public function addField(pinax_dataAccessDoctrine_DbField $field );

    // serve per cambiare un parametro di un campo a runtime
    public function setFieldParam($fieldName, $param, $value)
    {
        $field = $this->getField($fieldName);
        $field->$param = $value;
    }

    public function setFieldValue($fieldName, $value)
    {
        $this->__set($fieldName, $value);
    }

    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    public function getField($fieldName)
    {
        return $this->fields[$fieldName];
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getFieldType($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName]->type : '';
    }

    public function getSiteId()
    {
        return pinax_ObjectValues::get('org.pinax', 'siteId');
    }

    public function setSiteField($fieldName)
    {
        if ($this->fieldExists($fieldName)) {
            $this->siteField = $fieldName;
        }
    }

    public function getSiteField()
    {
        return $this->siteField;
    }

    public function resetSiteField()
    {
        $this->siteField = null;
    }

    function loadFromArray($values, $useSet=false)
    {
        $this->dirty = true;
        if (!empty($values)) {
            $this->emptyRecord();

            if ($useSet) {
                foreach ($values as $k => $v) {
                    $this->$k = $v;
                }
            }
            else {
                foreach ($values as $k => $v) {
                    if (property_exists($this, $k)) {
                        $this->$k = $v;
                    }
                    $this->data->$k = $v;
                }
            }

            foreach ($this->relations as $k=>$v)
            {
                if (array_key_exists($k, $values))
                {
                    $this->$k = $values[$k];
                }
            }
        }
    }

    public function loadFromQuery($name, $params)
    {
        $it = $this->createRecordIterator();
        $newAr = $it->load($name, $params)->first();
        if ( $newAr ) {
            $this->loadFromArray($newAr->getRawData());
        }

        return $newAr ? $this : false;
    }

    /**
     * @param  array  $values
     * @param  boolean $isNew
     * @return boolean
     */
    public function validate($values = null, $isNew=false)
    {
        $values = $this->collectValidateFields($values, $isNew);
        $validationErrors = array();

        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];

            if (is_null($field->validator) || $field->key) {
                continue;
            }

            $validationResult = $field->validator->validate($field->description, $value, $field->defaultValue, $values);

            if (is_string($validationResult)) {
                $validationErrors[$field->description] = $validationResult;
            } else if (is_array($validationResult)) {
                $validationErrors = array_merge($validationErrors, $validationResult);
            }
        }

        if (!empty($validationErrors)) {
            throw new pinax_validators_ValidationException($validationErrors);
        }

        return true;
    }

    public function emptyRecord()
    {
        $this->data = new StdClass();
        // $this->virtualData = new StdClass();
        $this->modifiedFields = array();
        $this->dirty = false;
    }

    public function addRelation($options)
    {
        assert(isset($options['name']));

        if ( empty( $options['objectName'] ) )
        {
            $options['objectName'] = $this->getTableNameWithoutPrefix().'#'.$options['name'];
        }
        $this->relations[$options['name']] = $options;
        $this->{$options['bindTo']} = null;
    }

    protected function buildAllRelations($build = true)
    {
        if ($this->processRelations && !$this->relationBuilded)
        {
            $this->relationBuilded = true;

            // risolve le relazioni
            foreach ($this->relations as $k => $v)
            {
                $relation = pinax_dataAccessDoctrine_RelationFactory::createRelation($this, $v);
                if ( $build )  {
                    $relation->build();
                }
                $this->$k = $relation;
            }
        }
    }

    protected function saveAllRelations($preSave=true)
    {
        // TODO
        // quando si fa l'update anche delle relazioni
        // c'è da controllare che non si verifichino errori
        // in questo caso c'è da segnalarlo
        foreach ($this->relations as $k => $v) {
            if (is_object($this->$k)) {
                if ($preSave==true) {
                    $this->$k->preSave();
                } else {
                    $this->$k->postSave();
                }
            }
        }
    }

    protected function deleteAllRelations()
    {
        foreach ($this->relations as $k => $v) {
            if (is_object($this->$k)) {
                $this->$k->delete();
            }
        }

        $this->relationBuilded = false;
    }

    /**
     * @param  array  $options
     * @return boolean
     *
     * @throws pinax_dataAccessDoctrine_exceptions_DataAccessException|\Doctrine\DBAL\DBALException
     */
    public function find($options=array()) {
        $options = array_merge(get_object_vars($this->data), $options);
        $qb = $this->createQueryBuilder()
            ->select('*')
            ->setMaxResults(1);

        $conditionNumber = 0;
        foreach($options as $k=>$v) {
            if (is_null($v)) continue;
            if (!isset($this->fields[$k])) {
                throw pinax_dataAccessDoctrine_exceptions_DataAccessException::unknownColumn($k, $this->getTableName());
            }
            $valueParam = ":value".$conditionNumber++;
            $qb->andWhere($qb->expr()->eq($k, $valueParam));
            $qb->setParameter($valueParam, $this->convertIfDateType($k, $v));
        }

        if ($this->siteField && !isset($options[$this->siteField])) {
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


    public function __call($name, $arguments)
    {
        return $this->{$name};

    }

    public function __get($name)
    {
        if (property_exists($this->data, $name)) {
            $value = $this->data->$name;
            return array_key_exists($name, $this->fields) ? $this->fields[$name]->format($value, $this->connection) : $value;
        }
        else if (property_exists($this->virtualData, $name)) {
            $this->buildAllRelations();
            return $this->virtualData->$name;
        }
        else if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name]->defaultValue;
        }

        throw pinax_dataAccessDoctrine_ActiveRecordException::getFailed($this->tableName, $name);
    }


    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->fields)) {
            $field = $this->fields[$name];
            // La condizione verifica che il campo è stato modificato, non è un campo di sistema
            if ($this->$name !== $value && !$field->isSystemField) {
                $this->modifiedFields[$name] = true;
            }
            $this->data->$name = $this->convertIfDateType($name, $value);
        }
        else {
            $this->buildAllRelations();
            $this->virtualData->$name = $value;
        }
    }

    public function convertIfDateType($fieldName, $value)
    {
        if (!isset($this->fields[$fieldName])) {
            return $value;
        }
        $field = $this->fields[$fieldName];

        if ($field->type == Type::DATE || $field->type == Type::DATETIME) {
            return pinax_localeDate2ISO($value);
        } else {
            return $value;
        }
    }

    public function forceModified($name)
    {
         $this->modifiedFields[$name] = true;
    }

    public function getValues($getRelationValues=false, $getVirtualField=true, $encode=false, $systemFields=true)
    {
        $result = new StdClass;
        foreach ($this->data as $name => $value) {
            if ($systemFields == false && $this->fields[$name]->isSystemField) {
                continue;
            }
            $result->{$name} = array_key_exists($name, $this->fields) ? $this->fields[$name]->format($value, $this->connection) : $value;
        }

        if ($getRelationValues) {
            $this->buildAllRelations();
            foreach ($this->relations as $k => $v) {
                if (!is_object($this->$k)) {
                    $result->$k = $this->$k;
                } else {
                    if ( method_exists( $this->$k, 'collectFieldsValues' ) ) {
                        $result->$k = $this->$k->collectFieldsValues( $getVirtualField, $encode );
                    } else {
                        $result->$k = null;
                    }
                }
            }
        }

        foreach ($this->virtualData as $name => $value) {
            $result->{$name} = $value;
        }
        return $result;
    }

    public function getRawData()
    {
        return $this->data;
    }

    // restituisce anche i campi con valore null
    public function getValuesForced($getRelationValues=false, $getVirtualField=true, $encode=false, $systemFields=true)
    {
        $result = $this->getValues($getRelationValues, $getVirtualField, $encode, $systemFields);
        foreach($this->fields as $k=>$v) {
            if (!property_exists($result, $k)) {
                $result->$k = $v->defaultValue;
            }
        }
        return $result;
    }

    public function getValuesAsArray($getRelationValues=false, $getVirtualField=true, $encode=false, $systemFields=true)
    {
        $result = $this->getValues($getRelationValues, $getVirtualField, $encode, $systemFields);
        return get_object_vars($result);
    }

    function getFieldValue($name, $raw=false)
    {
        $this->buildAllRelations();
        return property_exists($this->data, $name) || property_exists($this->virtualData, $name) ? ($raw ? $this->data->$name : $this->$name) : '';
    }

    function getFieldValueByRegexp($name, $raw=false)
    {
        $this->buildAllRelations();

        foreach ($this->data as $k => $value) {
            if (strpos( $k, $name) !== false) {
                return $raw ? $this->data->$k : $this->$k;
            }
        }

        return '';
    }

    public function getId()
    {
        if (is_null($this->primaryKeyName)) {
            throw pinax_dataAccessDoctrine_ActiveRecordException::primaryKeyNotDefined($this->tableName);
        }
        $primarykey = $this->primaryKeyName;
        return $this->$primarykey;
    }

    public function setId($value)
    {
        $primarykey = $this->primaryKeyName;
        $this->$primarykey = $value;
    }

    public function isNew()
    {
        $primaryKeyValue = $this->{$this->primaryKeyName};
        return empty( $primaryKeyValue );
    }

    public function fieldExists($name)
    {
        return array_key_exists($name, $this->fields) || array_key_exists($name, (array)$this->virtualData);
    }

    public function keyInDataExists($name) {
        return property_exists($this->data, $name);
    }

    public function isModified($name) {
        return $this->modifiedFields[$name] == true;
    }

    /**
     * @return pinax_dataAccessDoctrine_RecordIterator
     */
    abstract public function createRecordIterator();

    /**
     * @param array $options
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    abstract public function createQueryBuilder($options=array());

    public function dump()
    {
        var_dump($this->getValuesAsArray());
    }

    abstract protected function loadSequenceName();


    /**
     * @param  array  $values
     * @param  boolean $isNew
     * @return boolean
     */
    abstract protected function collectValidateFields($values=null, $isNew=false);
}
