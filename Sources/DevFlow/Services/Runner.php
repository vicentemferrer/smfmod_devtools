<?php

namespace SMF\Mods\DevFlow\Services;

use SMF\Mods\DevFlow\AbstractMigration;
use SMF\Mods\DevFlow\DbLogger;
use SMF\Mods\DevFlow\Handlers\DirectHandler;

/**
 * Class Runner
 * Executes migrations safely using the DirectHandler.
 */
class Runner
{
    /**
     * @var DbLogger
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param DbLogger $logger
     */
    public function __construct(DbLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Runs the 'up' method of a migration.
     *
     * @param string $file Path to migration file.
     * @param string $className Class name of the migration.
     * @throws \Exception If migration fails.
     */
    public function up($file, $className)
    {
        $migration = $this->loadMigration($file, $className);

        // Execute up()
        try {
            $migration->up();
            // Log success
            $this->logger->log($className);
        } catch (\Exception $e) {
            // Re-throw to be handled by controller
            throw $e;
        }
    }

    /**
     * Runs the 'down' method of a migration.
     *
     * @param string $file Path to migration file.
     * @param string $className Class name of the migration.
     * @throws \Exception If migration fails.
     */
    public function down($file, $className)
    {
        $migration = $this->loadMigration($file, $className);

        // Execute down()
        try {
            $migration->down();
            // Log removal
            $this->logger->unlog($className);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Loads the migration file and instantiates the class.
     * Injects the DirectHandler for immediate execution.
     *
     * @param string $file
     * @param string $className
     * @return AbstractMigration
     * @throws \Exception
     */
    protected function loadMigration($file, $className)
    {
        if (!file_exists($file)) {
            throw new \Exception('Migration file not found: ' . $file);
        }

        require_once $file;

        if (!class_exists($className)) {
            throw new \Exception('Migration class not found: ' . $className . ' in file ' . $file);
        }

        $migration = new $className();

        if (!($migration instanceof AbstractMigration)) {
            throw new \Exception('Migration class ' . $className . ' must extend SMF\Mods\DevFlow\AbstractMigration');
        }

        // INJECT DIRECT HANDLER
        $migration->setHandler(new DirectHandler());

        return $migration;
    }
}
