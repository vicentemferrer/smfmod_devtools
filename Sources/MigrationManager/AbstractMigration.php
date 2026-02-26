<?php

namespace SMF\Mods\MigrationManager;

/**
 * Class AbstractMigration
 * Base class for all user migrations. Wraps SMF global functions into cleaner methods.
 */
abstract class AbstractMigration
{
    /**
     * Applies the changes.
     */
    abstract public function up();

    /**
     * Reverts the changes.
     */
    abstract public function down();

    /**
     * Optional description.
     */
    public function description()
    {
        return '';
    }

    // -------------------------------------------------------------------------
    // DATABASE HELPERS
    // -------------------------------------------------------------------------

    /**
     * Creates a database table.
     *
     * @param string $table_name Table name (use {db_prefix}).
     * @param array $columns
     * @param array $indexes
     * @param array $parameters
     * @param string $if_exists
     */
    protected function create_table($table_name, $columns, $indexes = [], $parameters = [], $if_exists = 'ignore')
    {
        global $smcFunc;
        $smcFunc['db_create_table']($table_name, $columns, $indexes, $parameters, $if_exists);
    }

    /**
     * Drops a table.
     *
     * @param string $table_name
     */
    protected function drop_table($table_name)
    {
        global $smcFunc;
        $smcFunc['db_drop_table']($table_name);
    }

    /**
     * Adds a column to a table.
     *
     * @param string $table_name
     * @param array $column_info
     */
    protected function add_column($table_name, $column_info)
    {
        global $smcFunc;
        $smcFunc['db_add_column']($table_name, $column_info);
    }

    /**
     * Removes a column from a table.
     *
     * @param string $table_name
     * @param string $column_name
     */
    protected function remove_column($table_name, $column_name)
    {
        global $smcFunc;
        $smcFunc['db_remove_column']($table_name, $column_name);
    }

    /**
     * Executes an arbitrary SQL query.
     *
     * @param string $identifier
     * @param string $query
     * @param array $params
     * @return mixed
     */
    protected function db_query($identifier, $query, $params = [])
    {
        global $smcFunc;
        return $smcFunc['db_query']($identifier, $query, $params);
    }

    // -------------------------------------------------------------------------
    // HOOK HELPERS
    // -------------------------------------------------------------------------

    /**
     * Adds an integration hook.
     *
     * @param string $hook
     * @param string $function
     * @param string $file
     * @param bool $object
     */
    protected function add_hook($hook, $function, $file = '', $object = false)
    {
        add_integration_function($hook, $function, true, $file, $object);
    }

    /**
     * Removes an integration hook.
     *
     * @param string $hook
     * @param string $function
     * @param string $file
     * @param bool $object
     */
    protected function remove_hook($hook, $function, $file = '', $object = false)
    {
        remove_integration_function($hook, $function, true, $file, $object);
    }

    // -------------------------------------------------------------------------
    // SETTINGS HELPERS
    // -------------------------------------------------------------------------

    /**
     * Updates mod settings.
     *
     * @param array $settings
     * @param bool $update
     */
    protected function update_settings($settings, $update = true)
    {
        updateSettings($settings, $update);
    }
}
