<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\DrawFrameworkExtraExtension;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawFrameworkExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawFrameworkExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'console' => [
                'enabled' => false,
            ],
            'mailer' => [
                'enabled' => false,
            ],
            'messenger' => [
                'enabled' => false,
            ],
            'security' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield ['draw.tester.command.coverage_check'];
        yield [TestsCoverageCheckCommand::class, 'draw.tester.command.coverage_check'];
    }
}
