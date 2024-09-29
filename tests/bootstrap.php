<?php

declare(strict_types=1);

require_once __DIR__.'/../config/bootstrap.php';

use Symfony\Component\ErrorHandler\ErrorHandler;

// Hack for PHPUnit 11.0.0
// see https://github.com/symfony/symfony/issues/53812
// can probably be removed on next Symfony 6.4.x release
$phpunitsHandler = set_error_handler(static fn () => null);
restore_exception_handler();
ErrorHandler::register(null);
set_error_handler($phpunitsHandler);
