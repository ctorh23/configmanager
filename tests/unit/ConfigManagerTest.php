<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManagerTests;

use Ctorh23\ConfigManager\ConfigManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConfigManager::class)]
final class ConfigManagerTest extends TestCase
{
    private static string $fixturesDir;

    public static function setUpBeforeClass(): void
    {
        self::$fixturesDir = \dirname(__DIR__) . '/fixtures/config';
    }

    /**
     * @covers ConfigManager::get()
     * @covers ConfigManager::getSimple()
     * @covers ConfigManager::set()
     * @covers ConfigManager::setSimple()
     * @covers ConfigManager::validateKey()
     */
    public function testGetSimpleKeyReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $confMan->set('app_name', 'MyApp');
        $this->assertEquals('MyApp', $confMan->get('app_name'));
    }

    /**
     * @covers ConfigManager::get()
     * @covers ConfigManager::getComplex()
     * @covers ConfigManager::validateKey()
     */
    public function testGetComplexKeyReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->assertEquals('MyApp', $confMan->get('app.name'));
    }

    /**
     * @covers ConfigManager::__construct()
     * @covers ConfigManager::validateDir()
     */
    public function testThrowsExceptionWhenInstantiatingWithNotAccessibleDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $confMan = new ConfigManager('/not/existing/dir');
    }
}
