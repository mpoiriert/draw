<?php

namespace Draw\Component\AwsToolKit\Tests\EventListener;

use Aws\Ec2\Ec2Client;
use Draw\Component\AwsToolKit\EventListener\NewestInstanceRoleCheckListener;
use Draw\Component\AwsToolKit\Imds\ImdsClientInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 */
#[CoversClass(NewestInstanceRoleCheckListener::class)]
class NewestInstanceRoleListenerCheckTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            static::createStub(Ec2Client::class),
            static::createStub(ImdsClientInterface::class),
        );

        static::assertSame(
            [
                ConsoleCommandEvent::class => [
                    ['checkNewestInstance', 50],
                ],
            ],
            $service::getSubscribedEvents()
        );
    }

    public function testCheckNewestInstanceNoOption(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            static::createStub(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::never())
            ->method('getCurrentInstanceId')
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, false),
                new NullOutput(),
            )
        );

        static::assertTrue($event->commandShouldRun());
    }

    public function testCheckNewestInstanceOptionNull(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            static::createStub(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::never())
            ->method('getCurrentInstanceId')
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true),
                new NullOutput(),
            )
        );

        static::assertTrue($event->commandShouldRun());
    }

    public function testCheckNewestInstanceCurrentInstanceIdError(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            static::createStub(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willThrowException(new \Exception())
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true, uniqid('role-')),
                new NullOutput(),
            )
        );

        static::assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceCurrentInstanceIdEmpty(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            static::createStub(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn(null)
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true, uniqid('role-')),
                new NullOutput(),
            )
        );

        static::assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceNoInstance(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            $ec2Client = $this->createMock(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn(uniqid('instance-id-'))
        ;

        $ec2Client->expects(static::once())
            ->method('__call')
            ->with(
                'describeInstances',
                self::provideDescribeInstancesArgs(
                    $role = uniqid('role-')
                )
            )
            ->willReturn([
                'Reservations' => [
                    [
                        'Instances' => [],
                    ],
                ],
            ])
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true, $role),
                new NullOutput(),
            )
        );

        static::assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceNotNewestInstance(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            $ec2Client = $this->createMock(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn($instanceId = uniqid('instance-id-'))
        ;

        $ec2Client->expects(static::once())
            ->method('__call')
            ->with(
                'describeInstances',
                self::provideDescribeInstancesArgs(
                    $role = uniqid('role-')
                )
            )
            ->willReturn([
                'Reservations' => [
                    [
                        'Instances' => [
                            [
                                'LaunchTime' => new \DateTimeImmutable('- 1 day'),
                                'InstanceId' => $instanceId,
                            ],
                            [
                                'LaunchTime' => new \DateTimeImmutable(),
                                'InstanceId' => uniqid('isntance-id-'),
                            ],
                        ],
                    ],
                ],
            ])
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true, $role),
                new NullOutput(),
            )
        );

        static::assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceError(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            $ec2Client = $this->createMock(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn(uniqid('instance-id-'))
        ;

        $ec2Client->expects(static::once())
            ->method('__call')
            ->with(
                'describeInstances',
                self::provideDescribeInstancesArgs(
                    $role = uniqid('role-')
                )
            )
            ->willThrowException(new \Exception())
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true, $role),
                new NullOutput(),
            )
        );

        static::assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceNewestInstance(): void
    {
        $service = new NewestInstanceRoleCheckListener(
            $ec2Client = $this->createMock(Ec2Client::class),
            $imdsClient = $this->createMock(ImdsClientInterface::class),
        );

        $imdsClient
            ->expects(static::once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn($instanceId = uniqid('instance-id-'))
        ;

        $ec2Client->expects(static::once())
            ->method('__call')
            ->with(
                'describeInstances',
                self::provideDescribeInstancesArgs(
                    $role = uniqid('role-')
                )
            )
            ->willReturn([
                'Reservations' => [
                    [
                        'Instances' => [
                            [
                                'LaunchTime' => new \DateTimeImmutable(),
                                'InstanceId' => $instanceId,
                            ],
                            [
                                'LaunchTime' => new \DateTimeImmutable('- 1 day'),
                                'InstanceId' => uniqid('instance-id-'),
                            ],
                        ],
                    ],
                ],
            ])
        ;

        $service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput($service, true, $role),
                new NullOutput(),
            )
        );

        static::assertTrue($event->commandShouldRun());
    }

    private function createInput(
        NewestInstanceRoleCheckListener $service,
        bool $hasOption,
        ?string $optionValue = null,
    ): InputInterface {
        $input = $this->createMock(InputInterface::class);

        $input->expects(static::once())
            ->method('hasOption')
            ->with($service::OPTION_AWS_NEWEST_INSTANCE_ROLE)
            ->willReturn($hasOption)
        ;

        if ($hasOption) {
            $input->expects(static::once())
                ->method('getOption')
                ->with($service::OPTION_AWS_NEWEST_INSTANCE_ROLE)
                ->willReturn($optionValue)
            ;
        } else {
            $input->expects(static::never())
                ->method('getOption')
                ->with($service::OPTION_AWS_NEWEST_INSTANCE_ROLE)
            ;
        }

        return $input;
    }

    private static function provideDescribeInstancesArgs(string $role): array
    {
        return [
            [
                'DryRun' => false,
                'Filters' => [
                    [
                        'Name' => 'tag:Name',
                        'Values' => [$role],
                    ],
                    [
                        'Name' => 'instance-state-name',
                        'Values' => ['running'],
                    ],
                ],
            ],
        ];
    }
}
