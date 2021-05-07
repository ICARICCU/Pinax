<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_DataAccess
{

    /**
     * Static array with connections
     */
    /** @var \Doctrine\DBAL\Connection[] $connection */
    private static $connections = array();
    private static $transactionStack = array();
    private static $transactionStackCounter = 0;

    private static $defaultTypesOverridden = false;
    private static $cacheDriver = null;

    public static function initCache()
    {

        $cacheDir = __Paths::get('CACHE') . 'doctrine';
        @mkdir($cacheDir);

        self::$cacheDriver = new \Doctrine\Common\Cache\PhpFileCache($cacheDir);
    }

    /**
     * @return \Doctrine\Common\Cache\PhpFileCache
     */
    public static function getCache()
    {

        return self::$cacheDriver;
    }

    /**
     * Creates a connection object based on the config values.
     * This method returns a Doctrine\DBAL\Connection which wraps the underlying
     * driver connection.
     *
     * @param  integer $n connection number
     * @return \Doctrine\DBAL\Connection
     * @throws Exception
     */
    public static function getConnection($n = 0)
    {
        $sufix = $n == 0 ? '' : '#' . $n;
        $dbType = __Config::get('DB_TYPE' . $sufix);

        if ($dbType==='none') {
            return null;
        }

        if (!isset( self::$connections['__' . $n] )) {
            // se non è settato a true si effettua l'override dei tipi standard di doctrine
            if (!self::$defaultTypesOverridden) {
                \Doctrine\DBAL\Types\Type::overrideType(\Doctrine\DBAL\Types\Type::BOOLEAN, 'pinax_dataAccessDoctrine_types_Boolean');
                \Doctrine\DBAL\Types\Type::overrideType(\Doctrine\DBAL\Types\Type::TARRAY, 'pinax_dataAccessDoctrine_types_Array');
                \Doctrine\DBAL\Types\Type::overrideType(\Doctrine\DBAL\Types\Type::TIME, 'pinax_dataAccessDoctrine_types_Time');
                \Doctrine\DBAL\Types\Type::overrideType(\Doctrine\DBAL\Types\Type::DATE, 'pinax_dataAccessDoctrine_types_Date');
                \Doctrine\DBAL\Types\Type::overrideType(\Doctrine\DBAL\Types\Type::DATETIME, 'pinax_dataAccessDoctrine_types_DateTime');
                \Doctrine\DBAL\Types\Type::overrideType(\Doctrine\DBAL\Types\Type::DATETIMETZ, 'pinax_dataAccessDoctrine_types_DateTimeTz');
                \Doctrine\DBAL\Types\Type::addType(pinax_dataAccessDoctrine_types_Types::ARRAY_ID, 'pinax_dataAccessDoctrine_types_ArrayID');
                self::$defaultTypesOverridden = true;

            }

            $dbSchema = null;
            switch ($dbType) {
                case 'mysql':
                    $params = array(
                        'driver'        => 'pdo_mysql',
                        'host'          => __Config::get('DB_HOST' . $sufix),
                        'user'          => __Config::get('DB_USER' . $sufix),
                        'password'      => __Config::get('DB_PSW' . $sufix),
                        'dbname'        => __Config::get('DB_NAME' . $sufix),
                        'driverOptions' => array()
                    );

                    $socket = __Config::get('DB_SOCKET' . $sufix);
                    if ($socket) {
                        $params['unix_socket'] = $socket;
                    }
                    if (__Config::get('DB_MYSQL_BUFFERED_QUERY' . $sufix)) {
                        $params['driverOptions'][PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
                    }
                    if (__Config::get('DB_ATTR_PERSISTENT' . $sufix)) {
                        $params['driverOptions'][PDO::ATTR_PERSISTENT] = true;
                    }

                    $charset = __Config::get('CHARSET');
                    if ($charset) {
                        if (strtolower($charset) == 'utf-8') {
                            $params['charset']                                     = 'utf8';
                            $params['driverOptions'][PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
                        } else {
                            $params['charset'] = $charset;
                        }
                    }

                    break;

                case 'sqlite':
                    $params = array(
                        'driver'   => 'pdo_sqlite',
                        'user'     => __Config::get('DB_USER' . $sufix),
                        'password' => __Config::get('DB_PSW' . $sufix),
                    );
                    $path   = __Config::get('DB_NAME' . $sufix);
                    if ($path == 'memory:') {
                        $params['memory'] = true;
                    } else {
                        $params['path'] = $path;
                    }
                    break;

                case 'pgsql':
                    list($dbName, $dbSchema) = explode('.', __Config::get('DB_NAME'));

                    $params = array(
                        'driver'   => 'pdo_pgsql',
                        'host'     => __Config::get('DB_HOST' . $sufix),
                        'user'     => __Config::get('DB_USER' . $sufix),
                        'password' => __Config::get('DB_PSW' . $sufix),
                        'dbname'   => $dbName
                    );
                    break;
            }

            if (__Config::get('DB_PORT' . $sufix)) {
                $params['port'] = __Config::get('DB_PORT' . $sufix);
            }


            $checkDbConnection = __Config::get('DB_CHECK_CONNECTION' . $sufix)===true;
            $attempt = 0;
            $lastException = null;
            while ($attempt < 3) {
                try {
                    $conn = \Doctrine\DBAL\DriverManager::getConnection($params);
                    if ($checkDbConnection) {
                        $conn->connect();
                    }
                    self::$connections['__' . $n] = $conn;
                    if ($dbType === 'pgsql' && isset($dbSchema)) {
                        $conn->executeQuery(sprintf('set search_path to %s;', $dbSchema));
                    }
                    break;
                } catch  (\PDOException $e) {
                    $attempt++;
                    $lastException = $e;
                }
            }
            if (!isset(self::$connections['__' . $n])) {
                $eventInfo = array('type' => PNX_LOG_EVENT,
                                   'data' => array(
                                       'level' => PNX_LOG_ERROR,
                                       'group' => 'pinax.sql',
                                       'message' => array('errorMessage' => $e->getMessage(), 'errorCode' => $e->getCode(), 'attempt' => $attempt)
                                   ));
                $evt = pinax_ObjectFactory::createObject( 'pinax.events.Event', pinax_ObjectValues::get('org.pinax', 'application'), $eventInfo );
                pinax_events_EventDispatcher::dispatchEvent( $evt );
                throw new Exception($lastException->getMessage(), $lastException->getCode(), $lastException);
            } else {
                $platform = self::$connections['__' . $n]->getDatabasePlatform();
                $platform->registerDoctrineTypeMapping('enum', 'string');
                $platform->registerDoctrineTypeMapping('bit', 'boolean');
            }

        }

        return self::$connections['__' . $n];
    }

    /**
     * @param string $name
     * @param string $className
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function addType($name, $className)
    {

        \Doctrine\DBAL\Types\Type::addType($name, $className);
    }

    /**
     * @param string $name
     * @return \Doctrine\DBAL\Types\Type
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getType($name)
    {

        return \Doctrine\DBAL\Types\Type::getType($name);
    }

    /**
     * Close a connection
     * @param  integer $n connection number
     */
    public static function close($n = 0)
    {

        $conn = self::getConnection($n);
        if ($conn->getTransactionNestingLevel()) {
            self::stackDump(false);
        }
        $conn->close();
        unset( self::$connections['__' . $n] );
    }

    /**
     * Close all connections
     */
    public static function closeAll()
    {
        self::transactionCheck();
        foreach (self::$connections as $connection) {
            $connection->close();
        }
        self::$connections = array();
    }

    /**
     * Get the table prefix for a connection, the table prefix is defined in config file
     * @param  integer $n connection number
     * @return string     table prefix
     */
    public static function getTablePrefix($n = 0)
    {

        return __Config::get($n == 0 ? 'DB_PREFIX' : 'DB_PREFIX#' . $n);
    }

    /**
     * @param int $n
     */
    public static function enableLogging($n = 0)
    {

        $conn   = self::getConnection($n);
        $config = $conn->getConfiguration();
        $config->setSQLLogger(new pinax_dataAccessDoctrine_logging_EchoSQLLogger());
    }

    /**
     * @param int $n
     */
    public static function disableLogging($n = 0)
    {

        $conn   = self::getConnection($n);
        $config = $conn->getConfiguration();
        $config->setSQLLogger(null);
    }

    /**
     * @param int $n
     * @param string $fileName
     * @param string $dbName
     * @param array $options
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function importSqlFile($n, $fileName, $dbName = null, $options = null)
    {

        $conn = self::getConnection();
        if ($dbName) {
            $sm = $conn->getSchemaManager();
            $sm->createDatabase($dbName);
        }
        unset( $options );

        $conn = self::getConnection($n);

        $lines = file($fileName);
        $sql   = '';

        foreach ($lines as $line) {
            if (preg_match('/^--|^\/\*|^\s*$/m', $line)) {
                continue;
            }

            $sql .= $line;

            if (preg_match('/;\n/', $line)) {
                $conn->exec($sql);
                $sql = '';
            }
        }

        if (strpos($sql, ';')) {
            $conn->exec($sql);
        }

        self::close($n);
    }

    /**
     * @param string $dbName
     */
    public static function dropDatabase($dbName)
    {

        $conn = self::getConnection();
        $sm   = $conn->getSchemaManager();
        $sm->dropDatabase($dbName);
    }

    /**
     * @param string $tableName
     * @param int $n
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function truncateTable($tableName, $n = 0)
    {

        $conn     = self::getConnection($n);
        $platform = $conn->getDatabasePlatform();
        $query    = $platform->getTruncateTableSQL($tableName);
        $conn->executeQuery($query);
    }

    // TODO: astrarre per tipo db
    /**
     * @param string $schema
     * @param int $n
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function changeSchema($schema, $n = 0)
    {

        $conn = self::getConnection($n);
        $conn->executeQuery('SET search_path = "' . $schema . '", public;');
    }

    /**
     * @param int $n
     *
     * @return bool
     */
    public static function beginTransaction($n = 0)
    {

        $conn = self::getConnection($n);

        self::pushStack('beginTransaction', $conn->getTransactionNestingLevel());

        try {
            $conn->beginTransaction();
            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * @param int $n
     *
     * @return bool
     */
    public static function commit($n = 0)
    {

        $conn = self::getConnection($n);

        self::pushStack('commit', $conn->getTransactionNestingLevel());

        try {
            $conn->commit();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * @param int $n
     *
     * @return bool
     */
    public static function rollBack($n = 0)
    {

        $conn = self::getConnection($n);

        self::pushStack('rollBack', $conn->getTransactionNestingLevel());

        // Il rollback va fatto solo se ho aperta una transazione
        if ($conn->getTransactionNestingLevel()) {
            self::stackDump(true);

            try {
                $conn->rollBack();
                $result = true;
            } catch (Exception $e) {
                $result = false;
            }
        }

        if (!$result) {
            /** @var pinax_application_Application $application */
            $application = pinax_ObjectValues::get('org.pinax', 'application');
            $application->log('Error during rollback', PNX_LOG_ERROR, 'pinax.sql');
        }

        return $result;

    }

    /**
     * @return bool
     */
    public static function transactionCheck()
    {
        $error = false;

        foreach (self::$connections as $connection) {
            if ($connection->getTransactionNestingLevel()) {
                $error = true;

            }
        }

        if ($error) {
            self::stackDump(false);
        }

        return $error;
    }

    /**
     * @param string $function
     * @param int $counter
     * @return bool
     */
    public static function pushStack($function, $counter=0)
    {

        $data = array(
            'caller'   => self::getCallingName(),
            'counter'  => $counter,
            'function' => $function
        );
        self::$transactionStack[self::$transactionStackCounter] = $data;
        self::$transactionStackCounter ++;

        return true;
    }

    /**
     * @return string
     */
    public static function getCallingName()
    {

        $trace  = debug_backtrace();
        $caller = $trace[3];

        if (isset( $caller['class'] )) {
            $result = $caller['class'] . '::' . $caller['function'];
        } else {
            $result = $caller['function'];
        }

        return $result;
    }

    /**
     * @param bool $rollback
     */
    public static function stackDump($rollback = false)
    {

        $stackReport              = array();
        $stackReport['errorInfo'] = "Transaction report";
        $stackReport['stack']     = serialize(self::$transactionStack);
        if ($rollback) {
            $stackReport['cond'] = 'rollback';
        } else {
            $stackReport['cond'] = 'open transaction';
        }
        /** @var pinax_application_Application $application */
        $application = pinax_ObjectValues::get('org.pinax', 'application');
        $application->log($stackReport, PNX_LOG_ERROR, 'pinax.sql');
    }
}

