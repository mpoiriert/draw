<?php

declare(strict_types=1);

namespace Draw\Component\Mailer\Tests\Mock;

use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;

class MockableEmailWriter implements EmailWriterInterface
{
    #[\Override]
    public static function getForEmails(): array
    {
        return [];
    }

    public function method1(): void
    {
    }

    public function method2(): void
    {
    }
}
