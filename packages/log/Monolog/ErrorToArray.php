<?php

namespace Draw\Component\Log\Monolog;

use Monolog\Formatter\JsonFormatter;

final class ErrorToArray
{
    private function __construct()
    {
    }

    public static function convert(\Throwable $error, bool $includeStacktraces = true): array
    {
        $formatter = new class($includeStacktraces) extends JsonFormatter {
            public function __construct(bool $includeStacktraces)
            {
                parent::__construct(includeStacktraces: $includeStacktraces);
            }

            public function normalizeException(\Throwable $e, int $depth = 0): array
            {
                return parent::normalizeException($e, $depth);
            }
        };

        return $formatter->normalizeException($error);
    }
}
