<?php

trait pinax_testing_phpUnit_ApplicationIntegrationTrait
{
    protected $application;

    /**
     * @return void
     */
    public function migrate($force=false)
    {
        if (getenv('TEST_FAST_MIGRATION')==='true') {
            define('TEST_MIGRATIONS_EXECUTED', true);
        }

        if (defined('TEST_MIGRATIONS_EXECUTED') && !$force) {
            $this->truncateTables();
            return;
        }

        @define('TEST_MIGRATIONS_EXECUTED', true);
        $command = 'composer migrate-test';

        exec($command, $output);
    }

    /**
     * @return void
     */
    public function initApplication($force=false)
    {
        if (defined('PINAX_TEST') && !$force) {
            return;
        }

        if (class_exists('pinax_Config', false)) {
            pinax_dataAccessDoctrine_DataAccess::close();
            pinax_closeApp();
        }

        define('PINAX_TEST', true);
        $appHost = isset($_SERVER['APP_HOST']) ? $_SERVER['APP_HOST'] : 'localhost';
        $applicationBuilderPaths = isset($_SERVER['APP_BUILDER_PATH']) ? $_SERVER['APP_BUILDER_PATH'] : 'application/scripts/application.php';
        $applicationBuilder = include($applicationBuilderPaths);
        $this->application = $applicationBuilder($appHost);
    }

    /**
     * @return void
     */
    private function truncateTables()
    {
        $this->initPinax();

        $tables = explode(',', $_SERVER['FAST_MIGRATION_TABLES']);
        $truncate = $_SERVER['FAST_MIGRATION_TRUNACATE']==='true';

        try {
            $connection = pinax_dataAccessDoctrine_DataAccess::getConnection();
            $platform = $c->getDatabasePlatform();
            $query = [];
            foreach($tables as $tableName) {
                $query[] = $truncate ? $platform->getTruncateTableSql($tableName) : sprintf('delete from %s', $tableName);
            }
            $connection->executeStatement(implode(';',$query));
        } catch (Exception $e) {
            throw $e;
            exit;
        }
    }

}
