<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManagerTests;

use Ctorh23\ConfigManager\ConfigManager;
use Ctorh23\ConfigManager\Exception\DirException;
use Ctorh23\ConfigManager\Exception\KeyException;
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
        $this->expectException(DirException::class);
        $confMan = new ConfigManager('/not/existing/dir');
    }

    /**
     * @covers ConfigManager::get()
     */
    public function testGetNotValidKeyThrowsException(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->expectException(KeyException::class);
        $confMan->get('.not_valid');
    }

    /**
     * @covers ConfigManager::set()
     */
    public function testSetNotValidKeyThrowsException(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->expectException(KeyException::class);
        $confMan->set('not_valid.', 'dummy_value');
    }

    /**
     * @covers ConfigManager::set()
     * @covers ConfigManager::setComplex()
     * @covers ConfigManager::get()
     * @covers ConfigManager::getComplex()
     */
    public function testSetComplexKeyReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $confMan->set('database.connection.pgsql.host', 'localhost');
        $this->assertEquals('localhost', $confMan->get('database.connection.pgsql.host'));
    }
}
