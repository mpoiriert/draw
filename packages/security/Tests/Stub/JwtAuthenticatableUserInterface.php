<?php

declare(strict_types=1);

namespace Draw\Component\Security\Tests\Stub;

use Symfony\Component\Security\Core\User\UserInterface;

interface JwtAuthenticatableUserInterface extends UserInterface
{
    public function getJwtIdentifier(): ?string;
}
