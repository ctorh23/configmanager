<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager;

final class ConfigManager
{
    /**
     * The directory where configuration files resides.
     */
    private string $configDir;

    public function __construct(string $configDir)
    {
        $configDir = \rtrim($configDir, \DIRECTORY_SEPARATOR);

        if (!$this->verifyDir($configDir)) {
            throw new \InvalidArgumentException("The directory {$configDir} doesn not exist or is not accessible!");
        }

        $this->configDir = $configDir;
    }

    /**
     * Returns a configuration value.
     */
    public function get(string $key): mixed
    {
        return 'MyApp';
    }

    /**
     * Verify that the given argument is directory and is accessible.
     */
    private function verifyDir(string $dir): bool
    {
        return \is_dir($dir) && \is_readable($dir);
    }
}
