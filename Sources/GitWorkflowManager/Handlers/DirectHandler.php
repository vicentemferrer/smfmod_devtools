<?php

namespace SMF\Mods\GitWorkflowManager\Handlers;

use SMF\Mods\GitWorkflowManager\MigrationHandlerInterface;

/**
 * Class DirectHandler
 * Executes migration instructions immediately using SMF globals.
 * Used for local development.
 */
class DirectHandler implements MigrationHandlerInterface
{
    public function addHook($hook, $function, $file, $object)
    {
        add_integration_function($hook, $function, true, $file, $object);
    }

    public function removeHook($hook, $function, $file, $object)
    {
        remove_integration_function($hook, $function, true, $file, $object);
    }

    public function createTable($name, $columns, $indexes, $parameters, $if_exists)
    {
        global $smcFunc;
        $smcFunc['db_create_table']($name, $columns, $indexes, $parameters, $if_exists);
    }

    public function dropTable($name)
    {
        global $smcFunc;
        $smcFunc['db_drop_table']($name);
    }

    public function addColumn($table, $column_info)
    {
        global $smcFunc;
        $smcFunc['db_add_column']($table, $column_info);
    }

    public function removeColumn($table, $column_name)
    {
        global $smcFunc;
        $smcFunc['db_remove_column']($table, $column_name);
    }

    public function changeColumn($table, $column_name, $column_info)
    {
        global $smcFunc;
        $smcFunc['db_change_column']($table, $column_name, $column_info);
    }

    public function addIndex($table, $index_info)
    {
        global $smcFunc;
        $smcFunc['db_add_index']($table, $index_info);
    }

    public function removeIndex($table, $index_name)
    {
        global $smcFunc;
        $smcFunc['db_remove_index']($table, $index_name);
    }

    public function dbQuery($identifier, $query, $params)
    {
        global $smcFunc;
        return $smcFunc['db_query']($identifier, $query, $params);
    }

    public function updateSettings($settings, $update)
    {
        updateSettings($settings, $update);
    }
}
