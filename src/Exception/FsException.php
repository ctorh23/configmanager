<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager\Exception;

/**
 * Thrown when there is a problem with the config directory.
 *
 * @author Stoyan Dimitrov
 */
final class FsException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct(string $msg, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }

    public static function directoryNotAccessible(string $dir): self
    {
        return new self(\sprintf("The directory '%s' does not exist or is not accessible!", $dir));
    }
}
