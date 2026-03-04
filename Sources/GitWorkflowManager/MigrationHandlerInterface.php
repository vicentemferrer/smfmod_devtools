<?php

namespace SMF\Mods\GitWorkflowManager;

/**
 * Interface MigrationHandlerInterface
 * Defines the contract for migration handlers (Direct Execution vs Recording).
 */
interface MigrationHandlerInterface
{
    // Hooks
    public function addHook($hook, $function, $file, $object);
    public function removeHook($hook, $function, $file, $object);

    // Database
    public function createTable($name, $columns, $indexes, $parameters, $if_exists);
    public function dropTable($name);
    public function addColumn($table, $column_info);
    public function removeColumn($table, $column_name);
    public function changeColumn($table, $column_name, $column_info);
    public function addIndex($table, $index_info);
    public function removeIndex($table, $index_name);
    public function dbQuery($identifier, $query, $params);

    // Settings
    public function updateSettings($settings, $update);
}
