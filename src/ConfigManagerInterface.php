<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager;

/**
 * The main configuration management class.
 *
 * @author Stoyan Dimitrov
 */
interface ConfigManagerInterface
{
    /**
     * Reads a configuration value.
     */
    public function get(string $key): mixed;

    /**
     * Creates a configuration value.
     */
    public function set(string $key, mixed $value): self;
}
