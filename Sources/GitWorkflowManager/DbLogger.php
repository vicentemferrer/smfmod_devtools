<?php

namespace SMF\Mods\GitWorkflowManager;

/**
 * Class DbLogger
 * Handles logging applied migrations to the database.
 */
class DbLogger
{
    /**
     * @var string The table name for storing migration logs.
     */
    protected $table_name = '{db_prefix}gwm_log';

    /**
     * Ensures the migration log table exists.
     */
    public function ensureTableExists()
    {
        global $smcFunc;

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

        $smcFunc['db_create_table']($this->table_name, $columns, $indexes, [], 'ignore');
    }

    /**
     * Returns an array of all applied migration versions.
     *
     * @return array
     */
    public function getAppliedMigrations()
    {
        global $smcFunc;

        // Ensure table exists before querying (Lazy Init)
        $this->ensureTableExists();

        $request = $smcFunc['db_query']('', '
            SELECT version
            FROM {db_prefix}gwm_log',
            []
        );

        $versions = [];
        while ($row = $smcFunc['db_fetch_assoc']($request)) {
            $versions[] = $row['version'];
        }
        $smcFunc['db_free_result']($request);

        return $versions;
    }

    /**
     * Logs a migration as applied.
     *
     * @param string $version
     */
    public function log($version)
    {
        global $smcFunc;

        $smcFunc['db_insert']('replace',
            $this->table_name,
            ['version' => 'string', 'applied_at' => 'int'],
            [$version, time()],
            ['id_migration']
        );
    }

    /**
     * Removes a migration from the log.
     *
     * @param string $version
     */
    public function unlog($version)
    {
        global $smcFunc;

        $smcFunc['db_query']('', '
            DELETE FROM {db_prefix}gwm_log
            WHERE version = {string:version}',
            [
                'version' => $version,
            ]
        );
    }
}
