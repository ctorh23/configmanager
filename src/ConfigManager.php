<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager;

use Ctorh23\ConfigManager\Exception\FsException;
use Ctorh23\ConfigManager\Exception\ValidationException;

/**
 * The main configuration management class.
 *
 * @author Stoyan Dimitrov
 */
final class ConfigManager implements ConfigManagerInterface
{
    /**
     * The separator for the complex configuration keys.
     */
    private const KEY_SEPARATOR = '.';

    /**
     * The directory where configuration files resides.
     */
    private string $configDir;

    /**
     * The array containing configuration values.
     *
     * @var array<string, mixed>
     */
    private array $settings = [];

    /**
     * @throws FsException
     */
    public function __construct(string $configDir)
    {
        $configDir = \rtrim($configDir, \DIRECTORY_SEPARATOR);

        if (!$this->validateDir($configDir)) {
            throw FsException::directoryNotAccessible($configDir);
        }

        $this->configDir = $configDir;
    }

    /**
     * Reads a configuration value.
     *
     * @throws ValidationException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->validateKey($key)) {
            throw ValidationException::badKeyBoundary($key, self::KEY_SEPARATOR);
        }

        if ($this->isKeyComplex($key)) {
            return $this->getComplex($key) ?? $default;
        } else {
            return $this->getSimple($key) ?? $default;
        }
    }

    /**
     * Creates a configuration value.
     *
     * @throws ValidationException
     */
    public function set(string $key, mixed $value): self
    {
        if (!$this->validateKey($key)) {
            throw ValidationException::badKeyBoundary($key, self::KEY_SEPARATOR);
        }

        if ($this->isKeyComplex($key)) {
            $this->setComplex($key, $value);
        } else {
            $this->setSimple($key, $value);
        }

        return $this;
    }

    /**
     * Reads environment variable.
     */
    public function env(string $var, bool|int|float|string|null $default = null): bool|int|float|string|null
    {
        if (isset($_ENV[$var])) {
            return $this->castValue($_ENV[$var]);
        } elseif (($value = \getenv($var)) !== false) {
            return $this->castValue($value);
        } else {
            return $default;
        }
    }

    /**
     * Reads configuration value with complex key.
     *
     * @throws ValidationException
     */
    private function getComplex(string $key): mixed
    {
        if (!$this->isKeyComplex($key)) {
            throw ValidationException::complexMethodSimpleKey(__METHOD__, self::KEY_SEPARATOR);
        }

        $keys = $this->splitKey($key);
        $filename = \array_shift($keys);

        if (!isset($this->settings[$filename]) && !$this->loadFile($filename)) {
            return null;
        }

        $value = $this->settings[$filename];
        foreach ($keys as $k) {
            if (!\is_array($value) || !\array_key_exists($k, $value)) {
                $value = null;
                break;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Reads configuration value with simple key.
     *
     * @throws ValidationException
     */
    private function getSimple(string $key): mixed
    {
        if ($this->isKeyComplex($key)) {
            throw ValidationException::simpleMethodComplexKey(__METHOD__, self::KEY_SEPARATOR);
        }

        return $this->settings[$key] ?? null;
    }

    /**
     * Creates configuration value with complex key.
     *
     * @throws ValidationException
     */
    private function setComplex(string $key, mixed $value): void
    {
        if (!$this->isKeyComplex($key)) {
            throw ValidationException::complexMethodSimpleKey(__METHOD__, self::KEY_SEPARATOR);
        }

        $keys = $this->splitKey($key);
        $confItem = &$this->settings;
        for ($i = 0, $cnt = \count($keys); $i < $cnt - 1; $i++) {
            if (!\is_array($confItem)) {
                $confItem = [];
            }
            $confItem[$keys[$i]] ??= [];
            $confItem = &$confItem[$keys[$i]];
        }

        if (!\is_array($confItem)) {
            $confItem = [];
        }
        $confItem[$keys[$cnt - 1]] = $value;
    }

    /**
     * Creates configuration value with simple key.
     *
     * @throws ValidationException
     */
    private function setSimple(string $key, mixed $value): void
    {
        if ($this->isKeyComplex($key)) {
            throw ValidationException::simpleMethodComplexKey(__METHOD__, self::KEY_SEPARATOR);
        }

        $this->settings[$key] = $value;
    }

    /**
     * Determines if the key is complex or simple, i.e. containing or missing the KEY_SEPARATOR character.
     */
    private function isKeyComplex(string $key): bool
    {
        return \str_contains($key, self::KEY_SEPARATOR);
    }

    /**
     * Split the key based on the KEY_SEPARATOR constant.
     *
     * @return array<string>
     */
    private function splitKey(string $key): array
    {
        return \explode(self::KEY_SEPARATOR, $key);
    }

    /**
     * Loads file with configuration settings.
     *
     * @throws ValidationException
     */
    private function loadFile(string $filename): bool
    {
        $filePath = $this->configDir . DIRECTORY_SEPARATOR . $filename . '.php';
        if (\is_file($filePath) && \is_readable($filePath)) {
            $fileContent = require $filePath;
            if (!\is_array($fileContent)) {
                throw ValidationException::badConfigFileContent($filePath);
            }
            $this->settings[$filename] = $fileContent;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Explicit type casting.
     */
    private function castValue(mixed $value): bool|int|float|string|null
    {
        if (\is_numeric($value)) {
            return 0 + $value;
        }

        return match ($value) {
            'null' => null,
            'true' => true,
            'false' => false,
            default => \strval($value),
        };
    }

    /**
     * Ensure that the given argument is directory and is accessible.
     */
    private function validateDir(string $dir): bool
    {
        return \is_dir($dir) && \is_readable($dir);
    }

    /**
     * Ensure that a configuration key does not start or end with the KEY_SEPARATOR character.
     */
    private function validateKey(string $key): bool
    {
        return !\str_starts_with($key, self::KEY_SEPARATOR) && !\str_ends_with($key, self::KEY_SEPARATOR);
    }
}
