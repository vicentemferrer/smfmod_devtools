<?php

namespace SMF\Mods\GitWorkflowManager;

/**
 * Class Integration
 * Handles SMF hooks and integration logic.
 */
class Integration
{
    /**
     * Hook: integrate_admin_areas
     * Adds the 'Git Workflow Manager' area to the admin panel.
     *
     * @param array $admin_areas
     */
    public static function hook_admin_areas(&$admin_areas)
    {
        global $txt;

        // Load our language file
        loadLanguage('GitWorkflowManager');

        // Add 'gwm' to the 'maintenance' section
        $admin_areas['maintenance']['areas']['gwm'] = [
            'label' => $txt['gwm_title'],
            'function' => [Controller::class, 'main'],
            'icon' => 'server.png', // Standard SMF icon
            'permission' => ['admin_forum'],
            'subsections' => [
                'list' => [$txt['gwm_list_migrations']],
            ],
        ];
    }

    /**
     * Simple autoloader for the GitWorkflowManager namespace.
     * Can be called manually or registered via hook.
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(function ($class) {
            $prefix = 'SMF\\Mods\\GitWorkflowManager\\';
            $base_dir = __DIR__ . '/';

            // Does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            // Get the relative class name
            $relative_class = substr($class, $len);

            // Replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append with .php
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // If the file exists, require it
            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
