<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager;

final class ConfigManager
{
    private string $configDir;

    public function __construct(string $configDir)
    {
        $configDir = \rtrim($configDir, \DIRECTORY_SEPARATOR);

        if (!$this->verifyDir($configDir)) {
            throw new \InvalidArgumentException("The directory {$configDir} doesn not exist or is not accessible!");
        }

        $this->configDir = $configDir;
    }

    public function get(string $key): mixed
    {
        return 'MyApp';
    }

    private function verifyDir($dir): bool
    {
        return \is_dir($dir) && \is_readable($dir);
    }
}
