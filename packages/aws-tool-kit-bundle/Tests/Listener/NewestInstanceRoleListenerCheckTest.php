<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\Listener;

use Aws\Ec2\Ec2Client;
use DateTimeImmutable;
use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientInterface;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleCheckListener;
use Draw\Bundle\AwsToolKitBundle\Tests\TestCase;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Exception;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleCheckListener
 */
class NewestInstanceRoleListenerCheckTest extends TestCase
{
    private NewestInstanceRoleCheckListener $service;

    private Ec2Client $ec2Client;

    private ImdsClientInterface $imdsClient;

    public function setUp(): void
    {
        $this->service = clone $this->getService(NewestInstanceRoleCheckListener::class);

        ReflectionAccessor::setPropertiesValue(
            $this->service,
            [
                'imdsClient' => $this->imdsClient = $this->createMock(ImdsClientInterface::class),
            ]
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                ConsoleCommandEvent::class => [
                    ['checkNewestInstance', 50],
                ],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testCheckNewestInstanceNoOption(): void
    {
        $this->imdsClient
            ->expects($this->never())
            ->method('getCurrentInstanceId');

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(false),
                new NullOutput(),
            )
        );

        $this->assertTrue($event->commandShouldRun());
    }

    public function testCheckNewestInstanceOptionNull(): void
    {
        $this->imdsClient
            ->expects($this->never())
            ->method('getCurrentInstanceId');

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true),
                new NullOutput(),
            )
        );

        $this->assertTrue($event->commandShouldRun());
    }

    public function testCheckNewestInstanceCurrentInstanceIdError(): void
    {
        $this->imdsClient
            ->expects($this->once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willThrowException(new Exception());

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true, uniqid('role-')),
                new NullOutput(),
            )
        );

        $this->assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceCurrentInstanceIdEmpty(): void
    {
        $this->imdsClient
            ->expects($this->once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn(null);

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true, uniqid('role-')),
                new NullOutput(),
            )
        );

        $this->assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceNoInstance(): void
    {
        $role = uniqid('role-');
        $this->imdsClient
            ->expects($this->once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn(uniqid('instance-id-'));

        $this->mockEc2ClientDescribeInstances(
            $role,
            []
        );

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true, $role),
                new NullOutput(),
            )
        );

        $this->assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceNotNewestInstance(): void
    {
        $role = uniqid('role-');
        $this->imdsClient
            ->expects($this->once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn($instanceId = uniqid('instance-id-'));

        $this->mockEc2ClientDescribeInstances(
            $role,
            [
                [
                    'LaunchTime' => new DateTimeImmutable('- 1 day'),
                    'InstanceId' => $instanceId,
                ],
                [
                    'LaunchTime' => new DateTimeImmutable(),
                    'InstanceId' => uniqid('isntance-id-'),
                ],
            ]
        );

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true, $role),
                new NullOutput(),
            )
        );

        $this->assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceError(): void
    {
        $role = uniqid('role-');
        $this->imdsClient
            ->expects($this->once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn(uniqid('instance-id-'));

        $this->mockEc2ClientDescribeInstances(
            $role,
            [],
            new Exception()
        );

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true, $role),
                new NullOutput(),
            )
        );

        $this->assertFalse($event->commandShouldRun());
    }

    public function testCheckNewestInstanceNewestInstance(): void
    {
        $role = uniqid('role-');
        $this->imdsClient
            ->expects($this->once())
            ->method('getCurrentInstanceId')
            ->with()
            ->willReturn($instanceId = uniqid('instance-id-'));

        $this->mockEc2ClientDescribeInstances(
            $role,
            [
                [
                    'LaunchTime' => new DateTimeImmutable(),
                    'InstanceId' => $instanceId,
                ],
                [
                    'LaunchTime' => new DateTimeImmutable('- 1 day'),
                    'InstanceId' => uniqid('instance-id-'),
                ],
            ]
        );

        $this->service->checkNewestInstance(
            $event = new ConsoleCommandEvent(
                null,
                $this->createInput(true, $role),
                new NullOutput(),
            )
        );

        $this->assertTrue($event->commandShouldRun());
    }

    private function mockEc2ClientDescribeInstances(
        string $role,
        array $instances,
        ?Exception $error = null
    ): void {
        $ec2Client = $this->getMockBuilder(Ec2Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['describeInstances'])
            ->getMock();

        ReflectionAccessor::setPropertyValue(
            $this->service,
            'ec2Client',
            $this->ec2Client = $ec2Client
        );

        $invocationMocker = $this->ec2Client
            ->expects($this->once())
            ->method('describeInstances')
            ->with(
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
                ]
            );

        if ($error) {
            $invocationMocker->willThrowException($error);
        } else {
            $invocationMocker
                ->willReturn([
                    'Reservations' => [
                        [
                            'Instances' => $instances,
                        ],
                    ],
                ]);
        }
    }

    private function createInput(bool $hasOption, ?string $optionValue = null): InputInterface
    {
        $input = $this->createMock(InputInterface::class);

        $input->expects($this->once())
            ->method('hasOption')
            ->with($this->service::OPTION_AWS_NEWEST_INSTANCE_ROLE)
            ->willReturn($hasOption);

        if ($hasOption) {
            $input->expects($this->once())
                ->method('getOption')
                ->with($this->service::OPTION_AWS_NEWEST_INSTANCE_ROLE)
                ->willReturn($optionValue);
        } else {
            $input->expects($this->never())
                ->method('getOption')
                ->with($this->service::OPTION_AWS_NEWEST_INSTANCE_ROLE);
        }

        return $input;
    }
}
