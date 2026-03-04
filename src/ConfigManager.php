<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager;

class ConfigManager
{
    private string $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function get(string $key): mixed
    {
        return 'MyApp';
    }
}
