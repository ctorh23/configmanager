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
     */
    public function get(string $key): mixed
    {
        if (!$this->validateKey($key)) {
            throw ValidationException::badKeyBoundary($key, self::KEY_SEPARATOR);
        }

        if ($this->isKeyComplex($key)) {
            return $this->getComplex($key);
        } else {
            return $this->getSimple($key);
        }
    }

    /**
     * Creates a configuration value.
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
     * Reads configuration value with complex key.
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
            $value = $value[$k] ?? null;
        }

        return $value;
    }

    /**
     * Reads configuration value with simple key.
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
     */
    private function setComplex(string $key, mixed $value): void
    {
        if (!$this->isKeyComplex($key)) {
            throw ValidationException::complexMethodSimpleKey(__METHOD__, self::KEY_SEPARATOR);
        }

        $keys = $this->splitKey($key);
        $confItem = &$this->settings;
        for ($i = 0, $cnt = \count($keys); $i < $cnt - 1; $i++) {
            $confItem[$keys[$i]] ??= [];
            $confItem = &$confItem[$keys[$i]];
        }
        $confItem[$keys[$cnt - 1]] = $value;
    }

    /**
     * Creates configuration value with simple key.
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
