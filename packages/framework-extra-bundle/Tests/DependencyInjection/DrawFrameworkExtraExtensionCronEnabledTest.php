<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Application\Command\CronDumpToFileCommand;
use Draw\Component\Application\CronManager;
use Symfony\Component\DependencyInjection\Definition;

class DrawFrameworkExtraExtensionCronEnabledTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['cron'] = [
            'enabled' => true,
            'jobs' => [
                'acme_cron' => [
                    'description' => 'Execute acme:command every 5 minutes',
                    'command' => 'acme:command',
                    'expression' => '*/5 * * * *',
                    'enabled' => false,
                ],
            ],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.cron.command.dump_to_file'];
        yield [CronDumpToFileCommand::class, 'draw.cron.command.dump_to_file'];
        yield ['draw.cron.manager'];
        yield [CronManager::class, 'draw.cron.manager'];
    }

    public function testCronManagerDefinition(): void
    {
        $definition = $this->getContainerBuilder()
            ->getDefinition('draw.cron.manager');

        $methodCalls = $definition->getMethodCalls();

        $this->assertCount(1, $methodCalls);

        $this->assertSame(
            'addJob',
            $methodCalls[0][0]
        );

        $this->assertCount(
            1,
            $methodCalls[0][1]
        );

        $jobDefinition = $methodCalls[0][1][0];

        $this->assertInstanceOf(Definition::class, $jobDefinition);

        $this->assertSame(
            [
                'acme_cron',
                'acme:command',
                '*/5 * * * *',
                false,
                'Execute acme:command every 5 minutes',
            ],
            $jobDefinition->getArguments()
        );

        $this->assertSame(
            [
                [
                    'setOutput',
                    [
                        '>/dev/null 2>&1',
                    ],
                ],
            ],
            $jobDefinition->getMethodCalls()
        );
    }
}
