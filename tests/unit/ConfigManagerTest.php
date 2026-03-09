<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManagerTests;

use Ctorh23\ConfigManager\ConfigManager;
use Ctorh23\ConfigManager\Exception\FsException;
use Ctorh23\ConfigManager\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\BackupGlobals;

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
        $this->assertEquals('UTC', $confMan->get('app_tz', 'UTC'));
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
        $this->assertNull($confMan->get('database.connections.pgsql.collation'));
        $this->assertNull($confMan->get('db.host'));
        $this->assertEquals('utf8', $confMan->get('database.connections.pgsql.charset', 'utf8'));
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

    /**
     * @covers ConfigManager::env()
     * @covers ConfigManager::castValue()
     */
    #[BackupGlobals(true)]
    public function testReadEnvVar(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);

        \putenv('PGSQL_USER=app_user');
        $_ENV['PGSQL_PASSWORD'] = 'this-is-a-secret';
        \putenv('PGSQL_PORT=5432');
        \putenv('MYSQL_PERSISTENT=false');
        \putenv('MYSQL_PREFIX_INDEXES=true');
        \putenv('MYSQL_ENGINE=null');
        \putenv('SLA_UPTIME=99.9');

        $this->assertSame('app_user', $confMan->env('PGSQL_USER'));
        $this->assertSame('this-is-a-secret', $confMan->env('PGSQL_PASSWORD'));
        $this->assertSame(5432, $confMan->env('PGSQL_PORT'));
        $this->assertSame(false, $confMan->env('MYSQL_PERSISTENT'));
        $this->assertSame(true, $confMan->env('MYSQL_PREFIX_INDEXES'));
        $this->assertSame(null, $confMan->env('MYSQL_ENGINE'));
        $this->assertSame(99.9, $confMan->env('SLA_UPTIME'));
        $this->assertSame('utf8', $confMan->env('PGSQL_CHARSET', 'utf8'));
        $this->assertNull($confMan->env('MYSQL_USER'));
        $this->assertNull($confMan->env(''));
        $this->assertSame(0, $confMan->env('', 0));
    }

    /**
     * @covers ConfigManager::env()
     */
    #[BackupGlobals(true)]
    public function testUseEnvVarInConfigFile(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);

        $_ENV['SESSION_TIMEOUT'] = 900;

        $this->assertSame('redis', $confMan->get('session.driver'));
        $this->assertSame(900, $confMan->get('session.timeout'));
        $this->assertNull($confMan->get('session.encrypt'));
    }

    /**
     * @covers ConfigManager::getComplex()
     */
    public function testGetDeeperLevelThanExisting(): void
    {
        $confMan = new ConfigManager(self::$fixturesDir);

        $confMan->set('db.conn.mariadb', false);
        $this->assertNull($confMan->get('db.conn.mariadb.host.master'));
    }
}
