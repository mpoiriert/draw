<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\DrawFrameworkExtraExtension;
use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawFrameworkExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawFrameworkExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield ['draw.process.factory'];
        yield [ProcessFactoryInterface::class, 'draw.process.factory'];
        yield ['draw.security.role_restricted_authenticator_listener'];
        yield [RoleRestrictedAuthenticatorListener::class, 'draw.security.role_restricted_authenticator_listener'];
        yield ['draw.tester.command.coverage_check'];
        yield [TestsCoverageCheckCommand::class, 'draw.tester.command.coverage_check'];
    }
}
