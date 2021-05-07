<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class pinax_dataAccessDoctrine_SchemaManager
{
    private $sm;
    private $cache;
    protected $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
        $this->sm = $connection->getSchemaManager();
        $this->cache = pinax_ObjectFactory::createObject('pinax.cache.CacheFunction', $this, __Config::get('pinax.dataAccess.schemaManager.cacheLife'), true);
    }

    public function getTableNames()
    {
        return $this->sm->listTableNames();
    }

    public function getColumnNames($tableName)
    {
        $columns = $this->sm->listTableColumns($tableName);
        $names = array();

        foreach ($columns as $column) {
            $names[] = $column->getName();
        }

        return $names;
    }

    public function getSequenceName($tableName)
    {
        $sm = $this->sm;
        $method = __METHOD__.get_class($this->sm->getDatabasePlatform()).$this->conn->getHost().$this->conn->getDatabase();
        return $this->cache->get($method, func_get_args(), function() use ($tableName, $sm) {
            $columns = $sm->listTableColumns($tableName);

            foreach ($columns as $column) {
                $options = $column->getPlatformOptions();

                if (!empty($options) && isset($options['sequence'])) {
                    return $options['sequence'];
                }
            }
            return null;
        });
    }

    public function getFields($tableName)
    {
        $sm = $this->sm;
        $that = $this;
        $method = __METHOD__.get_class($this->sm->getDatabasePlatform()).$this->conn->getHost().$this->conn->getDatabase();
        return $this->cache->get($method, func_get_args(), function() use ($tableName, $sm, $that) {
            // recupera gli indici per cercare la chiave primaria
            $indexes = $sm->listTableIndexes($tableName);


            // cerca la chiave primaria
            foreach ($indexes as $index) {
                if ($index->isPrimary()) {
                    $indexColumns = array_flip($index->getColumns());
                    break;
                }
            }

            $fields = array();
            $columns = $sm->listTableColumns($tableName);
            foreach ($columns as $column) {
                $name = $column->getName();
                $size = !is_null($column->getLength()) ? $column->getLength() : $column->getPrecision();
                $key = isset($indexColumns[$name]);

                // serve per non avere errore quando la chiave primaria
                // è composta da più campi
                if ($key) {
                    $indexColumns = [];
                }

                // ($name, $type, $size, $key, $notNull, $defaultValue, $readFormat=true, $virtual=false)
                $fields[$name] = new pinax_dataAccessDoctrine_DbField(
                                            $name,
                                            $column->getType()->getName(),
                                            $size,
                                            $key,
                                            $that->getValidator($column),
                                            $column->getDefault(),
                                            true,
                                            false,
                                            $name
                                            );
            }

            return $fields;
        });
    }


    public function getValidator($column)
    {
        $validators = array();

        $type = $column->getType()->getName();

        if ($type == Type::INTEGER || $type == Type::SMALLINT || $type == Type::BIGINT) {
            $validators[] = new pinax_validators_Numeric();
        }
        else if ($type == Type::STRING || $type == Type::TEXT) {
            $validators[] = new pinax_validators_Text();
        }

        if ($column->getNotnull()) {
            $validators[] = new pinax_validators_NotNull();
        }

        if (empty($validators)) {
            return null;
        }
        else if (count($validators) == 1) {
            return $validators[0];
        }
        else {
            $composite = new pinax_validators_CompositeValidator();
            $composite->addArray($validators);
            return $composite;
        }
    }
}
