<?php

namespace Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ErrorToHttpCodeConverterInterface::class)]
interface ErrorToHttpCodeConverterInterface
{
    public static function getDefaultPriority(): int;

    public function convertToHttpCode(\Throwable $error): ?int;
}
