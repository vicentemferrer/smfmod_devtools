<?php

namespace SMF\Mods\MigrationManager;

/**
 * Class Integration
 * Handles SMF hooks and integration logic.
 */
class Integration
{
    /**
     * Hook: integrate_admin_areas
     * Adds the 'Migrations' area to the admin panel.
     *
     * @param array $admin_areas
     */
    public static function hook_admin_areas(&$admin_areas)
    {
        global $txt;

        // Load our language file
        loadLanguage('MigrationManager');

        // Add 'migrations' to the 'maintenance' section
        $admin_areas['maintenance']['areas']['migrations'] = [
            'label' => $txt['mm_migrations_title'],
            'function' => [Controller::class, 'main'],
            'icon' => 'server.png', // Standard SMF icon
            'permission' => ['admin_forum'],
            'subsections' => [
                'list' => [$txt['mm_list_migrations']],
            ],
        ];
    }

    /**
     * Simple autoloader for the Migration Manager namespace.
     * Can be called early in the request lifecycle if needed (e.g. integrate_pre_include)
     * But since we are only using it in admin, SMF will load this file when the hook is called,
     * so we can register the autoloader inside the hook function or rely on manual require in Controller.
     * For robustness, let's provide a static register method.
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(function ($class) {
            $prefix = 'SMF\\Mods\\MigrationManager\\';
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
