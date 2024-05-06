<?php

namespace Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter;

class ConfigurableErrorToHttpCodeConverter implements ErrorToHttpCodeConverterInterface
{
    private const DEFAULT_STATUS_CODE = 500;

    /**
     * @var array<string,int>
     */
    private array $errorCodes;

    public static function getDefaultPriority(): int
    {
        return -1000;
    }

    public function __construct(array $errorCodes = [])
    {
        $this->errorCodes = array_filter($errorCodes);
    }

    public function convertToHttpCode(\Throwable $error): int
    {
        $exceptionClass = $error::class;

        foreach ($this->errorCodes as $exceptionMapClass => $value) {
            switch (true) {
                case $exceptionClass === $exceptionMapClass:
                case is_a($error, $exceptionMapClass, true):
                    return $value;
            }
        }

        return self::DEFAULT_STATUS_CODE;
    }
}
