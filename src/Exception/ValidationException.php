<?php

declare(strict_types=1);

namespace Ctorh23\ConfigManager\Exception;

/**
 * Thrown when a domain-specific validation rule is violated.
 *
 * @author Stoyan Dimitrov
 */
final class ValidationException extends \DomainException implements ExceptionInterface
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

    public static function badConfigFileContent(string $filePath): self
    {
        return new self(\sprintf("The configuration file '%s' must return an array!", $filePath));
    }

    public static function notScalarValue(mixed $value): self
    {
        return new self(\sprintf("The type of the value you passed is '%s', but only scalar values or null are allowed!", \gettype($value)));
    }
}
