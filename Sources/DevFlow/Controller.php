<?php

namespace SMF\Mods\DevFlow;

use SMF\Mods\DevFlow\Services\Discoverer;
use SMF\Mods\DevFlow\Services\Runner;

/**
 * Class Controller
 * Main controller for the DevFlow admin page.
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
        loadTemplate('DevFlow');
        loadLanguage('DevFlow');

        // Initialize components
        $logger = new DbLogger();

        $migrations_dir = $boarddir . '/devflow_migrations';

        // Ensure directory exists
        if (!is_dir($migrations_dir)) {
            if (!mkdir($migrations_dir, 0755, true)) {
                 $context['df_error'] = sprintf($txt['df_dir_error'], $migrations_dir);
            } else {
                 // Secure it
                 file_put_contents($migrations_dir . '/.htaccess', 'Deny from all');
            }
        }

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
                    $context['df_success'] = sprintf($txt['df_applied_success'], $version);
                } catch (\Exception $e) {
                    $context['df_error'] = $e->getMessage();
                }
            } else {
                $context['df_error'] = $txt['df_file_not_found'];
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
                    $context['df_success'] = sprintf($txt['df_reverted_success'], $version);
                } catch (\Exception $e) {
                    $context['df_error'] = $e->getMessage();
                }
            } else {
                $context['df_error'] = $txt['df_file_not_found'];
            }
            $sa = 'list';
        }

        // Default action: List
        if ($sa === 'list') {
            $context['page_title'] = $txt['df_title'];
            $context['sub_template'] = 'devflow_list';

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
