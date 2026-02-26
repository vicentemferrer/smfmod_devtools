<?php

/**
 * DevFlow - Installation Script
 * This file installs the database table and hooks required for the DevFlow mod.
 * Run this once via CLI or browser.
 */

// If SSI.php is already loaded, we're good. Otherwise load it.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
    require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
    die('<b>Error:</b> Please verify you put this in the same place as SMF\'s index.php.');

// Database operations require smcFunc
global $smcFunc, $db_prefix;

echo 'Installing DevFlow...<br>';

// 1. Create the migrations log table
$tableName = '{db_prefix}devflow_log';

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
    'integrate_admin_areas' => 'SMF\\Mods\\DevFlow\\Integration::hook_admin_areas',
    'integrate_pre_include' => '$sourcedir/DevFlow/Integration.php', // Ensure autoloader is available
];

foreach ($hooks as $hook => $function) {
    add_integration_function($hook, $function, true);
}

echo 'Done.<br>';

// 3. Create migrations directory
$migrationsDir = dirname(__FILE__) . '/devflow_migrations';
if (!is_dir($migrationsDir)) {
    echo 'Creating migrations directory... ';
    if (mkdir($migrationsDir, 0755, true)) {
        file_put_contents($migrationsDir . '/.htaccess', 'Deny from all');
        echo 'Done.<br>';
    } else {
        echo 'Failed (Check permissions).<br>';
    }
}

echo '<b>Installation Complete!</b><br>';
echo 'You can now access DevFlow in the Admin Panel > Maintenance > DevFlow.<br>';
echo 'Please delete this file for security.';
