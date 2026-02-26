<?php

namespace SMF\Mods\MigrationManager;

use SMF\Mods\MigrationManager\Services\Discoverer;
use SMF\Mods\MigrationManager\Services\Runner;
use SMF\Mods\MigrationManager\DbLogger;

/**
 * Class Controller
 * Main controller for the Migration Manager admin page.
 */
class Controller
{
    /**
     * Main entry point.
     */
    public static function main()
    {
        global $context, $txt, $sourcedir, $boarddir;

        // Security check
        isAllowedTo('admin_forum');

        // Load necessary files
        loadTemplate('MigrationManager');
        loadLanguage('MigrationManager');

        // Initialize components
        $logger = new DbLogger();
        $logger->ensureTableExists(); // Ensure table exists on first run

        $migrations_dir = $boarddir . '/migrations';
        $discoverer = new Discoverer($migrations_dir);

        $runner = new Runner($logger);

        // Routing
        $sa = $_REQUEST['sa'] ?? 'list';

        if ($sa === 'apply') {
            checkSession('get');
            $version = $_REQUEST['version'] ?? '';
            $file = $migrations_dir . '/' . $version . '.php';

            if (file_exists($file)) {
                try {
                    $runner->up($file, $version);
                    $context['mm_success'] = sprintf($txt['mm_applied_success'], $version);
                } catch (\Exception $e) {
                    $context['mm_error'] = $e->getMessage();
                }
            } else {
                $context['mm_error'] = $txt['mm_file_not_found'];
            }
            // Refresh list
            $sa = 'list';
        } elseif ($sa === 'revert') {
            checkSession('get');
            $version = $_REQUEST['version'] ?? '';
            $file = $migrations_dir . '/' . $version . '.php';

            if (file_exists($file)) {
                try {
                    $runner->down($file, $version);
                    $context['mm_success'] = sprintf($txt['mm_reverted_success'], $version);
                } catch (\Exception $e) {
                    $context['mm_error'] = $e->getMessage();
                }
            } else {
                // If file is missing but log exists, we can force remove from log?
                // For now, error.
                $context['mm_error'] = $txt['mm_file_not_found'];
            }
            $sa = 'list';
        }

        // Default action: List
        if ($sa === 'list') {
            $context['page_title'] = $txt['mm_migrations_title'];
            $context['sub_template'] = 'migration_list';

            // Get all files
            $files = $discoverer->getMigrations();
            // Get applied migrations
            $applied = $logger->getAppliedMigrations();

            // Merge data
            $context['migrations'] = [];

            // First, add all files
            foreach ($files as $version => $info) {
                $is_applied = in_array($version, $applied);
                $context['migrations'][$version] = [
                    'version' => $version,
                    'status' => $is_applied ? 'applied' : 'pending',
                    'file' => $info['file'],
                    'timestamp' => $info['timestamp'],
                ];
            }

            // Check for orphaned logs (in DB but not file)
            foreach ($applied as $version) {
                if (!isset($context['migrations'][$version])) {
                    $context['migrations'][$version] = [
                        'version' => $version,
                        'status' => 'missing',
                        'file' => '',
                        'timestamp' => 0,
                    ];
                }
            }

            // Sort by version descending (newest first)
            krsort($context['migrations']);
        }
    }
}
