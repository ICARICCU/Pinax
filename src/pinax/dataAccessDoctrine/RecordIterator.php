<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class pinax_dataAccessDoctrine_RecordIterator extends pinax_dataAccessDoctrine_AbstractRecordIterator
{
    protected $conditionNumber;

    protected function resetQuery()
    {
        parent::resetQuery();
        $this->qb = $this->ar->createQueryBuilder();
        $this->conditionNumber = 0;
        $this->hasLimit = false;
        $this->hasSelect = false;
    }

    protected function processUnionFields($fieldName, $value)
    {
        if (strpos($fieldName, ',') !== false) {
            if (!empty( $value )) {
                $fields = explode(',', $fieldName);

                $v = array();
                foreach ($fields as $field) {
                    $v[] = array('field' => $field, 'value' => '%'.$value.'%', 'condition' => 'LIKE' );
                }
                $value = $v;
            }
        } else if (!$this->ar->fieldExists($fieldName)) {
            return '';
        }

        return $value;
    }

    public function execSql($sql, $options=array())
    {
        if (is_string($sql)) {
            $sql = array('sql' => $sql);
        }

// TODO controllare se nella query c'è già l'order
        $orderBy = $this->qb->getQueryPart('orderBy');
        $sql['sql'] .= $orderBy ? ' ORDER BY ' . implode(', ', $orderBy) : '';

        if (__Config::get('MULTISITE_ENABLED') && $this->ar->getSiteField()) {
            $siteField = $this->ar->getSiteField();
            $siteId = $this->ar->getSiteId();

            preg_match_all('/WHERE(.*?)( FROM | ORDER | GROUP |$)/si', $sql['sql'], $m);
            $lastMatch = count($m[0]) - 1;
            $sql['sql'] = str_replace($m[1][$lastMatch], ' '.$siteField.' = '.$siteId.' AND ('.$m[1][$lastMatch].') ', $sql['sql']);
        }

        if (isset($sql['filters']) && count($sql['filters'])) {
            $filtersSql = implode(' AND ', $sql['filters']);
            preg_match_all('/WHERE(.*?)( FROM | ORDER | GROUP |$)/si', $sql['sql'], $m);
            $lastMatch = count($m[0]) - 1;
            if ($lastMatch >= 0) {
                $sql['sql'] = str_replace($m[1][$lastMatch], '('.$m[1][$lastMatch].') AND ('.$filtersSql.') ', $sql['sql']);
            } else {
                $sql['sql'] .= ' WHERE '.$filtersSql;
            }
        }

        if (isset($options['replace'])) {
            foreach ($options['replace'] as $k => $v) {
                $sql['sql'] = str_replace($k, $v, $sql['sql']);
            }
        }

        $params = isset($options['params']) ? $options['params'] : ( is_array($options) ? $options : array());
        $params = isset($sql['params']) ? array_merge($sql['params'], $params) : $params;

        $this->executeSqlWithRowsCount($sql['sql'], $params);
    }

    protected function whereCondition($fieldName, $value, $condition = null, $composite = null)
    {
        $valueParam = ":value".$this->conditionNumber++;

        if (is_null($condition)) {
            if (is_null($composite)) {
                $this->qb->andWhere($this->expr->eq($fieldName, $valueParam));
            }
            else {
                $composite->add($this->expr->eq($fieldName, $valueParam));
            }
        }
        else {
            $fieldType = $this->ar->getFieldType($fieldName);
            $cast = $fieldType == Type::DATE || $fieldType == Type::DATETIME;

            if (is_null($composite)) {
                $this->qb->andWhere($this->expr->comparison($fieldName, $condition, $valueParam, $cast));
            }
            else {
                $composite->add($this->expr->comparison($fieldName, $condition, $valueParam, $cast));
            }
        }

        $this->qb->setParameter($valueParam, $value);

        return $this;
    }

    public function selectDistinct($fieldName, $fields=[])
    {
        $this->qb->resetQueryPart('select');
        call_user_func_array(array($this->qb, "select"), array_merge(array('DISTINCT('.$fieldName.')'), $fields));
        $this->hasSelect = true;
        return $this;
    }

    public function orderBy($fieldName, $order = 'ASC')
    {
        $this->qb->addOrderBy($fieldName, $order);
        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->qb->groupBy($groupBy);
        return $this;
    }

    public function exec()
    {
        if (!$this->querySqlToExec && !$this->hasSelect) {
            $this->select('*');
        }

        parent:: exec();
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function createCountQuery($sql) {
        $sql = preg_replace('/(order\s*by\s*.*)\s*$/i', '', $sql);
        preg_match_all('/^(\s*select\s*)(((distinct\s*[^,]*)|.*).*?)(\s*from.*)$/i', $sql, $match);
        if (count($match[0])!==1) {
            return 'SELECT count(*) as tot FROM ('.$sql.') as tempCountTable';
        }

        return $match[1][0].
                ' count('.($match[4][0] ? $match[4][0] : '*').') as tot '.
                $match[5][0];
    }
}
