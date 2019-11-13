<?php namespace Draw\Bundle\CronBundle\Tests\DependencyInjection;

use Draw\Bundle\CronBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends ConfigurationTestCase
{
    private const JOB_MINIMUM = [
        'name' => 'test',
        'expression' => '* * * * *',
        'command' => 'echo'
    ];

    private const JOB_DEFAULT = [
        'description' => null,
        'output' => '>/dev/null 2>&1',
        'enabled' => true
    ];

    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return ['jobs' => []];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['jobs' => [['expression' => '* * * * *', 'command' => 'echo']]],
            'Invalid configuration for path "draw_cron.jobs.0.name": You must specify a name for the job. Can be via the attribute or the key.'
        ];

        yield [
            ['jobs' => [['name' => 'test', 'command' => 'echo']]],
            'The child node "expression" at path "draw_cron.jobs.test" must be configured.'
        ];

        yield [
            ['jobs' => [['name' => 'test', 'expression' => '* * * * *']]],
            'The child node "command" at path "draw_cron.jobs.test" must be configured.'
        ];

        yield [
            ['jobs' => [array_merge(self::JOB_MINIMUM, ['enabled' => []])]],
            'Invalid type for path "draw_cron.jobs.test.enabled". Expected boolean, but got array.'
        ];

        yield [
            ['jobs' => [array_merge(self::JOB_MINIMUM, ['command' => []])]],
            'Invalid type for path "draw_cron.jobs.test.command". Expected scalar, but got array.'
        ];

        yield [
            ['jobs' => [array_merge(self::JOB_MINIMUM, ['output' => []])]],
            'Invalid type for path "draw_cron.jobs.test.output". Expected scalar, but got array.'
        ];

        yield [
            ['jobs' => [array_merge(self::JOB_MINIMUM, ['expression' => []])]],
            'Invalid type for path "draw_cron.jobs.test.expression". Expected scalar, but got array.'
        ];

        yield [
            ['jobs' => [array_merge(self::JOB_MINIMUM, ['description' => []])]],
            'Invalid type for path "draw_cron.jobs.test.description". Expected scalar, but got array.'
        ];
    }

    public function testJobNameAsKey()
    {
        $config = $this->processConfiguration([
            ['jobs' => [self::JOB_MINIMUM]]
        ]);

        $this->assertArrayHasKey(
            self::JOB_MINIMUM['name'],
            $config['jobs']
        );
    }

    public function testJobDefault()
    {
        $config = $this->processConfiguration([
            ['jobs' => [self::JOB_MINIMUM]]
        ]);

        $this->assertEquals(
            self::JOB_MINIMUM + self::JOB_DEFAULT,
            reset($config['jobs'])
        );
    }

    public function testJobKeyAsName()
    {
        $job = self::JOB_MINIMUM;
        $name = $job['name'];
        unset($job['name']);

        $config = $this->processConfiguration([
            ['jobs' => [$name => $job]]
        ]);

        $this->assertEquals(
            $name,
            reset($config['jobs'])['name']
        );
    }
}