<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManagerTests;

use Ctorh23\ConfigManager\ConfigManager;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testGetReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(__DIR__ . '/config');
        $this->assertEquals('MyApp', $confMan->get('app.name'));
    }
}
