<?php

namespace App\Tests\CronJob\EventListener;

use App\Command\NullCommand;
use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireTransportTester;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredCompletionAwareInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PostExecutionQueueCronJobListenerTest extends KernelTestCase implements AutowiredCompletionAwareInterface
{
    #[AutowireService]
    private NullCommand $command;

    #[AutowireTransportTester('async_high_priority')]
    private TransportTester $transportTester;

    private Application $application;

    public function postAutowire(): void
    {
        $this->application = new Application(static::$kernel);
        $this->application->add($this->command);
        $this->application->setAutoExit(false);
    }

    public function testTriggerCronJob(): void
    {
        $result = $this->application
            ->run(
                new ArrayInput([
                    'app:null',
                    '--draw-post-execution-queue-cron-job' => ['test', 'test'],
                ]),
                new NullOutput()
            );

        static::assertSame(
            Command::SUCCESS,
            $result
        );

        $this->transportTester
            ->assertMessageMatch(
                ExecuteCronJobMessage::class,
                Expression::andWhereEqual([
                    'execution.cronJob.name' => 'test',
                ]),
                2
            );
    }

    public function testTriggerCronJobError(): void
    {
        $result = $this->application
            ->run(
                new ArrayInput([
                    'app:null',
                    '--exit-code' => Command::FAILURE,
                    '--draw-post-execution-queue-cron-job' => ['test', 'test'],
                ]),
                new NullOutput()
            );

        static::assertSame(
            Command::FAILURE,
            $result
        );

        $this->transportTester
            ->assertMessageMatch(
                ExecuteCronJobMessage::class,
                count: 0
            );
    }
}
