<?php
$projectRoot = __DIR__.'/../../../../../';
require_once($projectRoot.'vendor/autoload.php');

if (count($argv)!==2) {
    die('ERRORE: Specificare il nome del seed'.PHP_EOL);
}

$applicationBuilderPaths = $projectRoot.'application/scripts/application.php';
if (!file_exists($applicationBuilderPaths)) {
	die('ERRORE: applicationBuilder script non trovato in "application/scripts/application.php"');
}
$applicationBuilder = include($applicationBuilderPaths);
$applicatin = $applicationBuilder();

$seedPath = sprintf('%sdatabase/seeds/%s.php', $projectRoot, $argv[1]);
if (!file_exists($seedPath)) {
    die('ERRORE: Nome del seed non valido'.PHP_EOL);
}
$connection = pinax_dataAccessDoctrine_DataAccess::getConnection();
include($seedPath);

/**
 * @param array $tables
 * @param bool $truncate
 * @return boolean
 */
function truncateTables(array $tables = [], bool $truncate = true): bool
{
    if (count($tables) === 0) {
        return false;
    }

    try {
        $connection = pinax_dataAccessDoctrine_DataAccess::getConnection();
        $platform = $connection->getDatabasePlatform();
        $query = [];
        foreach ($tables as $tableName) {
            $query[] = $truncate ? $platform->getTruncateTableSql($tableName) : sprintf('delete from %s', $tableName);
        }
        $connection->executeStatement(implode(';',$query));
    } catch (Exception $e) {
        var_dump($e->getMessage());
        return false;
    }

    return true;
}
