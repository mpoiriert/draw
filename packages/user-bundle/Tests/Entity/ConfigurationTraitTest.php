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
        $this->assertSame([], $this->getTwoFactorAuthenticationEnabledProviders());

        $this->setTwoFactorAuthenticationEnabledProviders(['totp', 'email', 'totp']);

        $this->assertSame(['totp', 'email'], $this->getTwoFactorAuthenticationEnabledProviders());
    }
}
