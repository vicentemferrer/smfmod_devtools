<?php

namespace SMF\Mods\DevFlow\Handlers;

use SMF\Mods\DevFlow\MigrationHandlerInterface;

/**
 * Class RecordingHandler
 * Records migration instructions for later export (e.g., to package-info.xml or install scripts).
 * Does NOT execute any changes on the current environment.
 */
class RecordingHandler implements MigrationHandlerInterface
{
    protected $hooks = [];
    protected $db_actions = [];
    protected $settings_actions = [];

    // --- Hooks ---
    public function addHook($hook, $function, $file, $object)
    {
        $this->hooks[] = [
            'type' => 'add',
            'hook' => $hook,
            'function' => $function,
            'file' => $file,
            'object' => $object
        ];
    }

    public function removeHook($hook, $function, $file, $object)
    {
        $this->hooks[] = [
            'type' => 'remove',
            'hook' => $hook,
            'function' => $function,
            'file' => $file,
            'object' => $object
        ];
    }

    // --- Database ---
    public function createTable($name, $columns, $indexes, $parameters, $if_exists)
    {
        $this->db_actions[] = [
            'method' => 'db_create_table',
            'args' => [$name, $columns, $indexes, $parameters, $if_exists]
        ];
    }

    public function dropTable($name)
    {
        $this->db_actions[] = [
            'method' => 'db_drop_table',
            'args' => [$name]
        ];
    }

    public function addColumn($table, $column_info)
    {
        $this->db_actions[] = [
            'method' => 'db_add_column',
            'args' => [$table, $column_info]
        ];
    }

    public function removeColumn($table, $column_name)
    {
        $this->db_actions[] = [
            'method' => 'db_remove_column',
            'args' => [$table, $column_name]
        ];
    }

    public function changeColumn($table, $column_name, $column_info)
    {
        $this->db_actions[] = [
            'method' => 'db_change_column',
            'args' => [$table, $column_name, $column_info]
        ];
    }

    public function addIndex($table, $index_info)
    {
        $this->db_actions[] = [
            'method' => 'db_add_index',
            'args' => [$table, $index_info]
        ];
    }

    public function removeIndex($table, $index_name)
    {
        $this->db_actions[] = [
            'method' => 'db_remove_index',
            'args' => [$table, $index_name]
        ];
    }

    public function dbQuery($identifier, $query, $params)
    {
        $this->db_actions[] = [
            'method' => 'db_query',
            'args' => [$identifier, $query, $params]
        ];
    }

    // --- Settings ---
    public function updateSettings($settings, $update)
    {
        $this->settings_actions[] = [
            'args' => [$settings, $update]
        ];
    }

    // --- Getters for the recorded data ---

    public function getHooks()
    {
        return $this->hooks;
    }

    public function getDbActions()
    {
        return $this->db_actions;
    }

    public function getSettingsActions()
    {
        return $this->settings_actions;
    }
}
