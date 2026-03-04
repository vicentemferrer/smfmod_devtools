<?php

/**
 * Git Workflow Manager - Database Setup Script
 * This file is executed by the SMF Package Manager during installation to create the necessary tables.
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
    require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
    die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s index.php.');

global $smcFunc;

// 1. Create the migrations log table
$tableName = '{db_prefix}gwm_log';

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

// Execute the table creation
$smcFunc['db_create_table']($tableName, $columns, $indexes, [], 'ignore');

?>