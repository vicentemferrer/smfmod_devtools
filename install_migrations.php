<?php

/**
 * Migration Manager - Installation Script
 * This file installs the database table and hooks required for the Migration Manager mod.
 * Run this once via CLI or browser.
 */

// If SSI.php is already loaded, we're good. Otherwise load it.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
    require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
    die('<b>Error:</b> Please verify you put this in the same place as SMF\'s index.php.');

// Database operations require smcFunc
global $smcFunc, $db_prefix;

echo 'Installing Migration Manager...<br>';

// 1. Create the migrations log table
$tableName = '{db_prefix}migrations_log';

$columns = [
    [
        'name' => 'id_migration',
        'type' => 'int',
        'auto' => true,
        'unsigned' => true,
    ],
    [
        'name' => 'version',
        'type' => 'varchar',
        'size' => 255,
    ],
    [
        'name' => 'applied_at',
        'type' => 'int',
        'unsigned' => true,
        'default' => 0,
    ],
];

$indexes = [
    [
        'type' => 'primary',
        'columns' => ['id_migration'],
    ],
    [
        'type' => 'unique',
        'columns' => ['version'],
    ],
];

echo 'Creating table ' . $tableName . '... ';
$smcFunc['db_create_table']($tableName, $columns, $indexes, [], 'ignore');
echo 'Done.<br>';

// 2. Register Hooks
echo 'Adding hooks... ';

$hooks = [
    'integrate_admin_areas' => 'SMF\\Mods\\MigrationManager\\Integration::hook_admin_areas',
    // We might need a pre_load hook to register the autoloader if we don't put it in the admin hook file directly
    // But since Integration.php is loaded by SMF when hook_admin_areas is triggered, we can handle autoloading there or add another hook.
    // For robustness, let's add a pre_include hook to ensure our autoloader is available early if needed.
    // 'integrate_pre_include' => '$sourcedir/MigrationManager/Integration.php', // This ensures the file is included
];

foreach ($hooks as $hook => $function) {
    add_integration_function($hook, $function, true);
}

echo 'Done.<br>';

// 3. Optional: Insert initial migration record if needed? No, let's start clean.

echo '<b>Installation Complete!</b><br>';
echo 'You can now access the Migration Manager in the Admin Panel > Maintenance > Migrations.<br>';
echo 'Please delete this file for security.';
