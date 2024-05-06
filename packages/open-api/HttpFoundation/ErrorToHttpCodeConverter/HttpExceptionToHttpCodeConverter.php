<?php

namespace Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter;

use Symfony\Component\HttpKernel\Exception\HttpException;

class HttpExceptionToHttpCodeConverter implements ErrorToHttpCodeConverterInterface
{
    public static function getDefaultPriority(): int
    {
        return 100;
    }

    public function convertToHttpCode(\Throwable $error): ?int
    {
        if ($error instanceof HttpException) {
            return $error->getStatusCode();
        }

        return null;
    }
}
