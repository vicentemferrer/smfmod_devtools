<?php

namespace SMF\Mods\GitWorkflowManager;

use SMF\Mods\GitWorkflowManager\Services\Discoverer;
use SMF\Mods\GitWorkflowManager\Services\Runner;
use SMF\Mods\GitWorkflowManager\Services\PackageGenerator;

/**
 * Class Controller
 * Main controller for the Git Workflow Manager admin page.
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
        loadTemplate('GitWorkflowManager');
        loadLanguage('GitWorkflowManager');

        // Initialize components
        $logger = new DbLogger();

        $migrations_dir = $boarddir . '/gwm_migrations';

        // Ensure directory exists
        if (!is_dir($migrations_dir)) {
            if (!mkdir($migrations_dir, 0755, true)) {
                 $context['gwm_error'] = sprintf($txt['gwm_dir_error'], $migrations_dir);
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
                    $context['gwm_success'] = sprintf($txt['gwm_applied_success'], $version);
                } catch (\Exception $e) {
                    $context['gwm_error'] = $e->getMessage();
                }
            } else {
                $context['gwm_error'] = $txt['gwm_file_not_found'];
            }
            $sa = 'list';
        } elseif ($sa === 'package') {
            checkSession('get');
            $version = $_REQUEST['version'] ?? '';
            $file = $migrations_dir . '/' . $version . '.php';

            if (file_exists($file)) {
                try {
                    $generator = new PackageGenerator();
                    $zipPath = $generator->generate($file, $version);

                    // Trigger download
                    if (file_exists($zipPath)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($zipPath));
                        readfile($zipPath);

                        // Clean up zip after download
                        unlink($zipPath);
                        exit; // Stop execution to prevent HTML output
                    } else {
                        $context['gwm_error'] = sprintf($txt['gwm_package_error'], 'Zip file not found after generation.');
                    }
                } catch (\Exception $e) {
                    $context['gwm_error'] = sprintf($txt['gwm_package_error'], $e->getMessage());
                }
            } else {
                $context['gwm_error'] = $txt['gwm_file_not_found'];
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
                    $context['gwm_success'] = sprintf($txt['gwm_reverted_success'], $version);
                } catch (\Exception $e) {
                    $context['gwm_error'] = $e->getMessage();
                }
            } else {
                $context['gwm_error'] = $txt['gwm_file_not_found'];
            }
            $sa = 'list';
        }

        // Default action: List
        if ($sa === 'list') {
            $context['page_title'] = $txt['gwm_title'];
            $context['sub_template'] = 'gwm_list';

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
