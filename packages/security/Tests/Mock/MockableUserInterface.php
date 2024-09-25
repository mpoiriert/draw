<?php

declare(strict_types=1);

namespace Draw\Component\Security\Tests\Mock;

use Symfony\Component\Security\Core\User\UserInterface;

interface MockableUserInterface extends UserInterface
{
    public function getIdentifierForJwtAuthenticatorTest(): ?string;
}
