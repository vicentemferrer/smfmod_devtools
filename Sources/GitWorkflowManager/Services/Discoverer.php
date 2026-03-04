<?php

namespace SMF\Mods\GitWorkflowManager\Services;

/**
 * Class Discoverer
 * Scans a directory for migration files.
 */
class Discoverer
{
    /**
     * @var string Path to migrations directory.
     */
    protected $directory;

    /**
     * Constructor.
     *
     * @param string $directory
     */
    public function __construct($directory)
    {
        $this->directory = rtrim($directory, '/\\');
    }

    /**
     * Returns a list of all migration files found.
     *
     * @return array An array of migration info: ['version' => '...', 'file' => '...']
     */
    public function getMigrations()
    {
        $files = glob($this->directory . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $filename = basename($file, '.php');

            $migrations[$filename] = [
                'version' => $filename,
                'file' => $file,
                'timestamp' => filemtime($file),
            ];
        }

        // Sort by version (filename)
        ksort($migrations);

        return $migrations;
    }
}
