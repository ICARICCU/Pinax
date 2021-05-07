<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class pinax_dataAccessDoctrine_AbstractRecordIterator extends PinaxObject implements pinax_dataAccessDoctrine_interfaces_IteratorInterface, Countable
{
    /**
     * @var pinax_dataAccessDoctrine_ActiveRecord
     */
    protected $ar;

    /**
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    protected $qb;
    protected $pos = 0;
    protected $statement = NULL;
    protected $data = NULL;
    protected $count = NULL;
    protected $EOF = false;
    protected $lastQuery;
    protected $querySqlToExec;
    protected $lastParams;
    protected $hasSelect;
    protected $hasLimit;
    protected $siteSet;
    protected $expr;

    protected $fetchMode = PDO::FETCH_BOTH;

    /**
     * @param pinax_dataAccessDoctrine_ActiveRecord $ar
     */
    public function __construct($ar)
    {
        $this->ar = $ar;
        $this->expr = $ar->getConnection()->getExpressionBuilder();
        $this->resetQuery();
    }

    public function getArType()
    {
        return $this->ar->getBaseClassName();
    }

    // serve per cambiare un parametro di un campo a runtime
    public function setFieldParam($fieldName, $param, $value)
    {
        $this->ar->setFieldParam($fieldName, $param, $value);
    }

    protected function resetQuery() {
        $this->siteSet = false;
    }

    public function expr() {
        return $this->expr;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function qb()
    {
        return $this->qb;
    }

    /**
     * @param string $query
     * @param array $params
     *
     * @return pinax_dataAccessDoctrine_interfaces_IteratorInterface
     *
     * @throws \BadMethodCallException|\Doctrine\DBAL\DBALException
     */
    public function load($query, $params=null)
    {
        $this->resetQuery();
        $driverName = $this->ar->getDriverName();

        if (method_exists($this->ar, 'query_'.$query)) {
            // NOTE: verificare se i parametri nel caso di query di tipo function
            // vengono passati nell'array params o come array diretto
            // così da pulire questa parte di codice
            $params = is_array($params) ?
                                (isset($params['params']) ? $params['params'] : $params)
                                : array($params);
            $params['iterator'] = $this;
            $r = pinax_helpers_PhpScript::callMethodWithParams($this->ar, 'query_'.$query, $params);
            if (is_a($r, 'pinax_dataAccessDoctrine_vo_SqlQueryVO')) {
                $this->querySqlToExec = $r->toArray();
            }
            unset($params['iterator']);
        } else if (method_exists($this->ar, 'querysql_'.$driverName.'_'.$query)) {
            $this->querySqlToExec = pinax_helpers_PhpScript::callMethodWithParams($this->ar, 'querysql_'.$driverName.'_'.$query);
        } else if (method_exists($this->ar, 'querysql_'.$query)) {
            $this->querySqlToExec = pinax_helpers_PhpScript::callMethodWithParams($this->ar, 'querysql_'.$query);
        } else if ('all'!==strtolower($query)) {
            throw new \BadMethodCallException("The query do not exists '".$query."'");
        }

        $this->data = null;
        $this->pos = 0;
        $this->EOF = false;
        $this->lastQuery = $query;
        $this->lastParams = $params;

        return $this;
    }

    public function setSqlQuery($query, $params=array())
    {
        $this->querySqlToExec = $query;
        $this->lastQuery = $query;
        $this->lastParams = $params;
    }

    abstract protected function processUnionFields($fieldName, $value);

    /**
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters($filters)
    {
        if ($this->querySqlToExec) {
            $filtersSql = array();
            foreach ($filters as $fieldName => $value) {
                if (strpos($fieldName, '__OR__')===0) {
                    $this->setOrFilters($value);
                    continue;
                }
// TODO implementare il quoting per postgress
                if (is_array($value)) {
                    if (isset($value['value']) && !is_array($value['value'])) {
                        $filtersSql[] = (isset($value['field']) ? $value['field'] : $fieldName).' '.$value['condition'].' "'.$value['value'].'"';
                    } else {
                        foreach ($value as $v) {
                            $filtersSql[] = $v['field'].' '.$v['condition'].' "'.$v['value'].'"';
                        }
                    }
                } else {
                    $filtersSql[] = $fieldName.' like "%'.$value.'%"';
                }
            }
            if (count($filtersSql)) {
                $this->querySqlToExec['filters'][] = '('.implode(' AND ', $filtersSql).')';
            }

            return $this;
        }

        foreach ($filters as $fieldName => $value) {
            if (strpos($fieldName, '__OR__')===0) {
                $this->setOrFilters($value);
                continue;
            }
            $newValue = $this->processUnionFields($fieldName, $value);

            if ($newValue === '' && is_int($fieldName)) {
                $this->qb->andWhere($value);
                continue;
            } else if ($newValue === '' or is_null($newValue)) {
                continue;
            }

            $value = !is_array($newValue) ? $this->whereConditionAsArray($fieldName, $newValue) : $newValue;


            if (isset($value['value']) && !is_array($value['value'])) {
                $this->where((isset($value['field']) ? $value['field'] : $fieldName), $value['value'], $value['condition']);
            } else {
                foreach ($value as $v) {
                    $this->where($v['field'], $v['value'], $v['condition']);
                }
            }
        }

        return $this;
    }

    public function setOrFilters($filters)
    {
        if ($this->querySqlToExec) {
// rimuovere duplicato con la parte sopra
            $filtersSql = array();
            foreach ($filters as $fieldName => $value) {
// TODO implementare il quoting per postgress
                if (is_array($value)) {
                    if (isset($value['value']) && !is_array($value['value'])) {
                        $filtersSql[] = (isset($value['field']) ? $value['field'] : $fieldName).' '.$value['condition'].' "'.$value['value'].'"';
                    } else {
                        foreach ($value as $v) {
                            $filtersSql[] = $v['field'].' '.$v['condition'].' "'.$v['value'].'"';
                        }
                    }
                } else {
                    $filtersSql[] = $fieldName.' like "%'.$value.'%"';
                }
            }
            if (count($filtersSql)) {
                $this->querySqlToExec['filters'][] = '('.implode(' OR ', $filtersSql).')';
            }
            return $this;
        }


        $or = $this->expr->orX();
        $added = false;
        foreach ($filters as $fieldName => $value) {
            $value = is_int($fieldName) && is_array($value) ? $value : $this->processUnionFields($fieldName, $value);

            if ($value == '' /*|| (is_array($value) && $value['value'] == '')  || !$this->ar->fieldExists($fieldName)*/) {
                continue;
            }
            $added = true;
            if (is_array($value)) {
                if (isset($value['value']) && !is_array($value['value'])) {
                    $this->where((isset($value['field']) ? $value['field'] : $fieldName), $value['value'], $value['condition'], $or);
                } else {
                    foreach ($value as $v) {
                        $this->where($v['field'], $v['value'], $v['condition'], $or);
                    }
                }
            }
            else {
                $this->where($fieldName, '%'.$value.'%', 'LIKE', $or);
            }
        }
        if ($added) {
            $this->qb->andWhere($or);
        }
        return $this;
    }

    public function setOrderBy($order) {
        if (is_string($order)) {
            $this->orderBy($order);
        } else {
            foreach ($order as $fieldName => $direction) {
                $this->orderBy($fieldName, $direction);
            }
        }

        return $this;
    }

    public function groupBy($field)
    {
        $this->qb->groupBy($field);
        return $this;
    }

    public function select($select)
    {
        $selects = is_array($select) ? $select : func_get_args();
        $this->qb->select($selects);
        $this->hasSelect = true;
        return $this;
    }

    public function addSelect($select = null)
    {
        $selects = is_array($select) ? $select : func_get_args();
        $this->qb->addSelect($selects);
        $this->hasSelect = true;
        return $this;
    }

    public function join($fromAlias, $join, $alias, $condition = null)
    {
        $this->qb->join($fromAlias, $join, $alias, $condition);
        return $this;
    }

    public function leftJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->qb->leftJoin($fromAlias, $join, $alias, $condition);
        return $this;
    }

    public function rightJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->qb->rightJoin($fromAlias, $join, $alias, $condition);
        return $this;
    }

    public function where($fieldName, $value = null, $condition = '=', $composite = null)
    {
        $driverName = $this->ar->getDriverName();

        if ($driverName == 'mysql' && strtoupper($condition) == 'ILIKE') {
            $condition = 'LIKE';
        }

        if (strpos($fieldName, 'MATCH')!==false) {
            $this->qb->andWhere($fieldName);
            return $this;
        }

        // se $value è null allora $fieldName contiene un'espressione del tipo 'nomecampo1 = valore1'
        if (is_null($value)) {
            list($fieldName, $condition, $value) = array_map('trim', explode(' ', $fieldName));
        }

        $value = $this->ar->convertIfDateType($fieldName, $value);

        return $this->whereCondition($fieldName, $value, $condition, $composite);
    }

    abstract protected function whereCondition($fieldName, $value, $condition = '=', $composite = null);

    public function whereSiteIs($value)
    {
        $this->qb->andWhere($this->expr->eq($this->ar->getSiteField(), ':site'))
                 ->setParameter(':site', $value);
        $this->siteSet = true;
        return $this;
    }

    public function whereIsNull($fieldName, $composite = null)
    {
        if (is_null($composite)) {
            $this->qb->andWhere($this->expr->isNull($fieldName));
        } else {
            $composite->add($this->expr->isNull($fieldName));
        }
        return $this;
    }

    public function disableSiteConditionIs()
    {
        $this->siteSet = true;
        return $this;
    }

    abstract public function orderBy($fieldName, $order = 'ASC');

    public function limit($offset, $limit = -1, $performCount=true)
    {
        if (is_array($offset)) {
            if (!isset($offset['start'])) {
                list($offset, $limit) = array_values($offset);
            } else {
                $limit = $offset['pageLength'];
                $offset = $offset['start'];
            }
        }
        $this->qb->setFirstResult($offset);
        $this->qb->setMaxResults($limit);
        $this->hasLimit = $performCount;
        return $this;
    }

    public function setParameter($param, $value)
    {
        $this->qb->setParameter($param, $value);
        return $this;
    }

    public function exec()
    {
        if ($this->querySqlToExec) {
            $this->execSql($this->querySqlToExec, $this->lastParams);
        }
        else {
            if (!$this->siteSet && $this->ar->getSiteField()) {
                $this->whereSiteIs($this->ar->getSiteid());
            }

            $this->lastQuery = $this->qb;
            $this->statement = $this->qb->execute();

            if ($this->hasLimit) {
                $qb = clone $this->qb;
                // resetta i limiti della query
                $qb->setFirstResult(null);
                $qb->setMaxResults(null);

                $select = implode(' ', $qb->getQueryPart('select'));
                if (stripos($select, 'distinct ') !== false || $qb->getQueryPart('distinct')) {
                    $qb->resetQueryParts(array('orderBy'));
                    $qb2 = $this->ar->getConnection()->createQueryBuilder();
                    $expr = $qb2->expr();

                    $qb2->select('COUNT(*) AS tot')
                        ->from('('.$qb->getSql().')', 'my_table')
                        ->setParameters($qb->getParameters());
                    $stmt = $qb2->execute();

                } else {
                    // elimina la parte select e orderBy perchè non servono per il conteggio
                    $qb->resetQueryParts(array('select', 'orderBy'));

                    // aggiunge il conteggio alla query
                    $qb->select('COUNT(*) AS tot');
                    $stmt = $qb->execute();
                }

                $row = $stmt->fetch();
                $this->count = $row['tot'];
            }
        }

        $this->resetQuery();

        return $this;
    }

    abstract public function execSql($sql, $options=array());

    public function next()
    {
        $this->fetch();
    }

    public function rewind()
    {
        if ($this->pos == 0) {
            $this->exec();
        } else if ($this->pos == 1) {
            // noting
            return;
        } else {
            if ($this->lastQuery instanceof Doctrine\DBAL\Query\QueryBuilder) {
                $this->statement = $this->lastQuery->execute();
            } else {
                $this->execSql($this->querySqlToExec, $this->lastParams);
            }
        }

        $this->pos = 0;
        $this->fetch();
    }

    public function valid()
    {
        return !$this->EOF;
    }

    public function key()
    {
        return $this->recordPos();
    }

    public function &first($returnNewIfNull=false)
    {
        $ar = $this->current();
        if ($returnNewIfNull && !$ar) {
            $ar = clone $this->ar;
        }
        return $ar;
    }

    public function &current()
    {
        if ($this->data == NULL) {
            $this->rewind();
        }

        // se non ci sono record
        if ($this->EOF) {
            $a = null;
            return $a;
        }

        $ar = clone $this->ar;
        $ar->loadFromArray($this->data);
        return $ar;
    }

    public function count()
    {
        if ($this->statement == NULL) {
            $this->rewind();
        }

        return $this->count == NULL ? $this->statement->rowCount() : $this->count;
    }

    public function setCount( $v )
    {
        $this->count = $v;
    }

    public function recordPos()
    {
        return $this->pos;
    }

    private function fetch()
    {
        if (!$this->statement) {
            dd(debug_backtrace());
        }
        $this->data = $this->statement->fetch($this->fetchMode);

        $this->EOF = $this->data === false;
        $this->pos++;

        if ($this->EOF) {
            $this->statement->closeCursor();
        }
    }

    /**
     * Return the query signature for identify and cache it
     * @return string signature
     */
    public function queryGroupForCaching()
    {
        return $this->ar->getTableName();
    }

    /**
     * Return the query signature for identify and cache it
     * @return string signature
     */
    public function querySignatureForCaching()
    {
        return $this->querySqlToExec ?
                md5(serialize($this->querySqlToExec).serialize($this->lastParams))
                :
                md5($this->qb->getSql());
    }

    /**
     *
     * @param string $fieldName
     * @param mixed $value
     * @return array
     */
    private function whereConditionAsArray($fieldName, $value)
    {
        $field = $this->ar->getField($fieldName);
        if ($field->type==='string' && $fieldName!=='fulltext') {
            return ['field' => $fieldName, 'value' => '%'.$value.'%', 'condition' => 'LIKE'];
        } else if ($field->type==='string' && $fieldName==='fulltext') {
            return ['field' => $fieldName, 'value' => $value.'*', 'condition' => '='];
        }

        return ['field' => $fieldName, 'value' => $value, 'condition' => '='];
    }

    /**
     * @param string $sql
     * @param array $params
     * @return void
     */
    protected function executeSqlWithRowsCount($sql, $params)
    {

        $connection = $this->ar->getConnection();

        if (__Config::get('pinax.dataAccess.sqlCount.new')) {
            $sqlToExec = $sql;
            if ($this->hasLimit) {
                $firstResult = $this->qb->getFirstResult();
                $maxResults = $this->qb->getMaxResults();
                $sqlToExec = $connection->getDatabasePlatform()->modifyLimitQuery($sqlToExec, $maxResults, $firstResult);
            }
            $this->statement = $connection->executeQuery($sqlToExec, $params);

            $sqlCount = $this->createCountQuery($sql);
            $stmt = $connection->executeQuery($sqlCount, $params);
            $row = $stmt->fetch();
            $this->count = $row['tot'];
            return;
        }

        $this->statement = $connection->executeQuery($sql, $params);
        $this->count = $this->statement->rowCount();

        $firstResult = $this->qb->getFirstResult();
        $maxResults = $this->qb->getMaxResults();
        if (!is_null($firstResult) && !is_null($maxResults)) {
            $sql = $connection->getDatabasePlatform()->modifyLimitQuery($sql, $maxResults, $firstResult);
            $this->statement = $connection->executeQuery($sql, $params);
        }
    }

    abstract protected function createCountQuery($sql);
}
