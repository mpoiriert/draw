<?php namespace Draw\Bundle\CronBundle\Tests\DependencyInjection;

use Draw\Bundle\CronBundle\Command\DumpToFileCommand;
use Draw\Bundle\CronBundle\CronManager;
use Draw\Bundle\CronBundle\DependencyInjection\DrawCronExtension;
use Draw\Bundle\CronBundle\Model\Job;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DrawCronExtensionTest extends TestCase
{
    private static $defaultJobConfiguration = [
        'name' => 'test',
        'command' => 'echo test',
        'expression' => '* * * * *'
    ];

    /**
     * @var DrawCronExtension
     */
    private $extension;

    public function setUp()
    {
        $this->extension = new DrawCronExtension();
    }

    public function provideTestHasServiceDefinition()
    {
        return [
            [CronManager::class],
            [DumpToFileCommand::class]
        ];
    }

    /**
     * @dataProvider provideTestHasServiceDefinition
     *
     * @param $id
     */
    public function testHasServiceDefinition($id)
    {
        $this->assertTrue($this->load([])->hasDefinition($id));
    }

    public function testLoadNoJob()
    {
        $containerBuilder = $this->load([]);

        $this->assertJobDefinition($containerBuilder, []);
    }

    public function testLoadDefaultJob()
    {
        $containerBuilder = $this->load([
            [
                'jobs' => [
                    self::$defaultJobConfiguration
                ]
            ]
        ]);

        $this->assertJobDefinition(
            $containerBuilder,
            [
                self::$defaultJobConfiguration
                + [
                    'output' => '>/dev/null 2>&1',
                    'enabled' => true,
                    'description' => null
                ]
            ]//Default value of the configuration
        );
    }

    public function testLoadConfiguredJob()
    {
        $jobConfiguration = self::$defaultJobConfiguration
            + ['output' => 'other', 'enabled' => false, 'description' => 'description'];

        $containerBuilder = $this->load([
            [
                'jobs' => [
                    $jobConfiguration
                ]
            ]
        ]);

        $this->assertJobDefinition(
            $containerBuilder,
            [$jobConfiguration]
        );
    }

    private function assertJobDefinition(ContainerBuilder $containerBuilder, array $jobConfigurations)
    {
        $definition = $containerBuilder->getDefinition(CronManager::class);
        $methodCalls = $definition->getMethodCalls();
        $this->assertCount(count($jobConfigurations), $methodCalls);

        foreach ($methodCalls as $key => $methodCall) {
            $jobConfiguration = $jobConfigurations[$key];
            $this->assertSame('addJob', $methodCall[0]);

            /** @var Definition $jobDefinition */
            $jobDefinition = $methodCall[1][0];
            $this->assertInstanceOf(Definition::class, $jobDefinition);
            $this->assertSame(Job::class, $jobDefinition->getClass());
            $this->assertSame(
                [
                    $jobConfiguration['name'],
                    $jobConfiguration['command'],
                    $jobConfiguration['expression'],
                    $jobConfiguration['enabled'],
                    $jobConfiguration['description']
                ],
                $jobDefinition->getArguments()
            );

            $jobMethodCalls = $jobDefinition->getMethodCalls();
            $this->assertCount(1, $jobMethodCalls);
            $this->assertSame('setOutput', $jobMethodCalls[0][0]);
            $this->assertSame($jobConfiguration['output'], $jobMethodCalls[0][1][0]);
        }
    }

    /**
     * @param array $config
     * @return ContainerBuilder
     */
    private function load(array $config)
    {
        $containerBuilder = new ContainerBuilder();
        $this->extension->load($config, $containerBuilder);
        return $containerBuilder;
    }
}