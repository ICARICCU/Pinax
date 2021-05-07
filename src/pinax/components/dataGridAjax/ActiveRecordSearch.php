<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_dataGridAjax_ActiveRecordSearch implements pinax_components_dataGridAjax_interfaces_Search
{
    public function search($options, $columns, $filters, $ordering, $paging)
    {
        $it = $this->getModelIterator(  $options['recordClassName'],
                                        $options['setFiltersToQuery']=='true',
                                        $options['query'],
                                        $options['fullTextSearch']=='true',
                                        $options['filterClass'],
                                        $options['queryOperator'],
                                        $filters);

        if ($ordering) {
            $it->orderBy($ordering['field'], $ordering['dir']);
        }

        if ($paging) {
            $it->limit($paging['start'], $paging['length']);
        }

        // pinax_dataAccessDoctrine_DataAccess::enableLogging();
        return $this->collectResults($it);
    }

    /**
     * @param  string $sSearch
     * @param  array $aColumns
     * @return array
     */
    private function getModelIterator($recordClassName, $setFiltersToQuery, $query, $fullTextSearch, $filterClassName, $queryOperator, $filters)
    {
        $it = pinax_ObjectFactory::createModelIterator($recordClassName);

        if ($it->getArType() === 'document') {
            $it->setOptions(array('type' => 'PUBLISHED_DRAFT'));
        }

        if ($setFiltersToQuery) {
            $it->load($query, array('filters' => $filters['simple']));
        } else  {
            $it->load($query);
            if (method_exists($it, 'showAll')) {
                $it->showAll();
            }

            if ($fullTextSearch && $filters['q']) {
                $it->where('fulltext', '%'.$filters['q'].'%', 'ILIKE');
            } else {
                $filterClass = $filterClassName ? pinax_ObjectFactory::createObject($filterClassName) : null;
                if ($filterClass) {
                    $filters = $filterClass->getFilters($filters['withCondition']);
                }

                if (!empty($filters['withCondition'])) {
                    if ($queryOperator === 'OR') {
                        $it->setOrFilters($filters['withCondition']);
                    } else {
                        $it->setFilters($filters['withCondition']);
                    }
                }
            }
        }

        return $it;
    }

    private function collectResults($it)
    {
        $result = [];
        foreach($it as $row) {
            $result[] = $row;
        }
        return ['items' => $result, 'total' => $it->count()];
    }
}
