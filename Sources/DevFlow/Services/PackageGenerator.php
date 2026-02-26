<?php

namespace SMF\Mods\DevFlow\Services;

use SMF\Mods\DevFlow\AbstractMigration;
use SMF\Mods\DevFlow\Handlers\RecordingHandler;

/**
 * Class PackageGenerator
 * Generates an SMF package (ZIP) from a migration.
 * Uses RecordingHandler to capture instructions instead of executing them.
 */
class PackageGenerator
{
    protected $tempDir;

    public function __construct()
    {
        global $boarddir;
        $this->tempDir = $boarddir . '/Packages/temp_devflow_' . time();
    }

    public function generate($file, $className)
    {
        // 1. Load Migration
        if (!file_exists($file)) {
            throw new \Exception('Migration file not found.');
        }
        require_once $file;
        $migration = new $className();

        if (!($migration instanceof AbstractMigration)) {
             throw new \Exception('Invalid migration class.');
        }

        // 2. Setup Recording Handlers
        $installRecorder = new RecordingHandler();
        $uninstallRecorder = new RecordingHandler();

        // 3. Record INSTALL (up)
        $migration->setHandler($installRecorder);
        $migration->up();

        // 4. Record UNINSTALL (down)
        $migration->setHandler($uninstallRecorder);
        $migration->down();

        // 5. Create Temp Directory
        if (!mkdir($this->tempDir, 0755, true)) {
            throw new \Exception('Could not create temp directory.');
        }

        try {
            // 6. Generate install_db.php
            $installCode = $this->generatePhpScript($installRecorder);
            file_put_contents($this->tempDir . '/install_db.php', $installCode);

            // 7. Generate uninstall_db.php
            $uninstallCode = $this->generatePhpScript($uninstallRecorder);
            file_put_contents($this->tempDir . '/uninstall_db.php', $uninstallCode);

            // 8. Generate package-info.xml
            $xml = $this->generatePackageXml($className, $installRecorder, $uninstallRecorder);
            file_put_contents($this->tempDir . '/package-info.xml', $xml);

            // 9. Zip it
            $zipPath = dirname($this->tempDir) . '/' . $className . '.zip';
            $this->createZip($this->tempDir, $zipPath);

            // Cleanup
            $this->removeDirectory($this->tempDir);

            return $zipPath;

        } catch (\Exception $e) {
            $this->removeDirectory($this->tempDir);
            throw $e;
        }
    }

    protected function generatePhpScript(RecordingHandler $recorder)
    {
        $php = "<?php\n\n";
        $php .= "if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))\n";
        $php .= "    require_once(dirname(__FILE__) . '/SSI.php');\n\n";
        $php .= "global \$smcFunc;\n\n";

        foreach ($recorder->getDbActions() as $action) {
            $method = $action['method'];
            $args = $action['args'];
            // Basic serialization of arguments to PHP code
            $exportedArgs = array_map(function($arg) {
                return var_export($arg, true);
            }, $args);

            $php .= "\$smcFunc['" . $method . "'](" . implode(', ', $exportedArgs) . ");\n";
        }

        return $php;
    }

    protected function generatePackageXml($name, RecordingHandler $install, RecordingHandler $uninstall)
    {
        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<!DOCTYPE package-info SYSTEM "http://www.simplemachines.org/xml/package-info">' . "\n";
        $xml .= '<package-info xmlns="http://www.simplemachines.org/xml/package-info" xmlns:smf="http://www.simplemachines.org/">' . "\n";

        $xml .= "\t<id>DevFlow:" . $name . "</id>\n";
        $xml .= "\t<name>" . $name . "</name>\n";
        $xml .= "\t<version>1.0</version>\n";
        $xml .= "\t<type>modification</type>\n";

        // Install Section
        $xml .= "\t<install>\n";
        foreach ($install->getHooks() as $hook) {
            $xml .= sprintf("\t\t<hook hook=\"%s\" function=\"%s\" file=\"%s\" object=\"%s\" />\n",
                $hook['hook'], $hook['function'], $hook['file'], $hook['object'] ? 'true' : 'false');
        }
        $xml .= "\t\t<code>install_db.php</code>\n";
        $xml .= "\t</install>\n";

        // Uninstall Section
        $xml .= "\t<uninstall>\n";
        foreach ($uninstall->getHooks() as $hook) {
             // In uninstall, we typically reverse hooks.
             // SMF <uninstall> block executes things.
             // If we recorded a 'removeHook' in down(), we want to execute a remove.
             // But SMF XML usually uses reverse="true" on <install> block logic for uninstalls.
             // However, since we have explicit down() logic, we can just list the hooks to be removed/added explicitly.
             // If down() says "removeHook", we output a hook tag with reverse="true" or just rely on the fact that removing a hook is an action.
             // Wait, standard SMF XML <hook> adds. <hook reverse="true"> removes.
             // If down() calls removeHook, we should output <hook reverse="true">.

             if ($hook['type'] === 'remove') {
                 $xml .= sprintf("\t\t<hook hook=\"%s\" function=\"%s\" file=\"%s\" object=\"%s\" reverse=\"true\" />\n",
                    $hook['hook'], $hook['function'], $hook['file'], $hook['object'] ? 'true' : 'false');
             } else {
                 // If down() adds a hook (weird but possible), standard tag.
                 $xml .= sprintf("\t\t<hook hook=\"%s\" function=\"%s\" file=\"%s\" object=\"%s\" />\n",
                    $hook['hook'], $hook['function'], $hook['file'], $hook['object'] ? 'true' : 'false');
             }
        }
        $xml .= "\t\t<code>uninstall_db.php</code>\n";
        $xml .= "\t</uninstall>\n";

        $xml .= '</package-info>';
        return $xml;
    }

    protected function createZip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);
                $file = str_replace('\\', '/', $file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    protected function removeDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
