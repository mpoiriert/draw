<?php

namespace Draw\Contracts\Application\Exception;

class ConfigurationIsNotAccessibleException extends \RuntimeException
{
    public function __construct(
        string $message = 'Configuration registry cannot access configuration',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
