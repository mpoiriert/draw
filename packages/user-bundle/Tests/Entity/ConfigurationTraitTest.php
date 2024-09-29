<?php

namespace Draw\Bundle\UserBundle\Tests\Entity;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ConfigurationTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConfigurationTraitTest extends TestCase
{
    use ConfigurationTrait;

    public function testTwoFactorAuthenticationEnabledProvidersMutator(): void
    {
        static::assertSame([], $this->getTwoFactorAuthenticationEnabledProviders());

        $this->setTwoFactorAuthenticationEnabledProviders(['totp', 'email', 'totp']);

        static::assertSame(['totp', 'email'], $this->getTwoFactorAuthenticationEnabledProviders());
    }
}
