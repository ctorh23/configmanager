<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager\Exception;

/**
 * Thrown when the configuration key does not match the naming restrictions.
 *
 * @author Stoyan Dimitrov
 */
final class KeyException extends \DomainException implements ExceptionInterface
{
    public function __construct(string $msg, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }

    public static function badKeyBoundary(string $key, string $character): self
    {
        return new self(\sprintf("The key %s cannot starts or ends with the '%s' character!", $key, $character));
    }

    public static function complexMethodSimpleKey(string $method, string $character): self
    {
        return new self(\sprintf("It is not allowed to call the method '%s' with a key missing the '%s' character!", $method, $character));
    }

    public static function simpleMethodComplexKey(string $method, string $character): self
    {
        return new self(\sprintf("It is not allowed to call the method '%s' with a key containing the '%s' character!", $method, $character));
    }
}
