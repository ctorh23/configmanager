<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManagerTests;

use Ctorh23\ConfigManager\ConfigManager;
use Ctorh23\ConfigManager\Exception\FsException;
use Ctorh23\ConfigManager\Exception\ValidationException;
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
     * @covers ConfigManager::isKeyComplex()
     */
    public function testGetSimpleKeyReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $confMan->set('app_name', 'MyApp');
        $this->assertEquals('MyApp', $confMan->get('app_name'));
        $this->assertNull($confMan->get('application_name'));
    }

    /**
     * @covers ConfigManager::get()
     * @covers ConfigManager::getComplex()
     * @covers ConfigManager::isKeyComplex()
     * @covers ConfigManager::splitKey()
     * @covers ConfigManager::loadFile()
     */
    public function testGetComplexKeyReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->assertEquals('MyApp', $confMan->get('app.name'));
        $this->assertEquals('pgsql.acme.com', $confMan->get('database.connections.pgsql.host'));
        $this->assertEquals(5432, $confMan->get('database.connections.pgsql.port'));
        $this->assertEquals('mariadb.acme.com', $confMan->get('database.connections.mariadb.host'));
        $this->assertEquals('utf8mb4', $confMan->get('database.connections.mariadb.charset'));
        $this->assertEquals('migrations', $confMan->get('database.migrations.table'));
        $this->assertNull($confMan->get('database.connections.pgsql.charset'));
        $this->assertNull($confMan->get('db.host'));
    }

    /**
     * @covers ConfigManager::__construct()
     * @covers ConfigManager::validateDir()
     */
    public function testThrowsExceptionWhenInstantiatingWithNotAccessibleDirectory(): void
    {
        $this->expectException(FsException::class);
        $confMan = new ConfigManager('/not/existing/dir');
    }

    /**
     * @covers ConfigManager::get()
     * @covers ConfigManager::validateKey()
     */
    public function testGetNotValidKeyThrowsException(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->expectException(ValidationException::class);
        $confMan->get('.not_valid');
    }

    /**
     * @covers ConfigManager::set()
     * @covers ConfigManager::validateKey()
     */
    public function testSetNotValidKeyThrowsException(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->expectException(ValidationException::class);
        $confMan->set('not_valid.', 'dummy_value');
    }

    /**
     * @covers ConfigManager::set()
     * @covers ConfigManager::setComplex()
     * @covers ConfigManager::get()
     * @covers ConfigManager::getComplex()
     * @covers ConfigManager::isKeyComplex()
     * @covers ConfigManager::splitKey()
     */
    public function testSetComplexKeyReturnsExpectedValue(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->assertEquals('pgsql.acme.com', $confMan->get('database.connections.pgsql.host'));
        $confMan->set('database.connection.pgsql.host', 'localhost');
        $this->assertEquals('localhost', $confMan->get('database.connection.pgsql.host'));
    }

    /**
     * @covers ConfigManager::getComplex()
     * @covers ConfigManager::loadFile()
     */
    public function testGetComplexThrowsExceptionWhenConfigFileIsWrong(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);
        $this->expectException(ValidationException::class);
        $this->assertEquals('MyApp', $confMan->get('wrong.name'));
    }
}
