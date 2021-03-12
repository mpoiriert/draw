<?php

namespace Draw\Bundle\AwsToolKitBundle\Listener;

use Aws\Ec2\Ec2Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

/**
 * This command listener is use to check if a command can be executed or not base on the fact it's the newest
 * instance in a specific role. This is useful when you want a cron to be executed only on one running instance in a pool
 * of instance and that all your server have the same cron configuration. This is check every time at runtime.
 *
 * Example:
 *   console/bin acme:purge-database --aws-newest-instance-role=prod
 */
class NewestInstanceRoleListener implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const OPTION_AWS_NEWEST_INSTANCE_ROLE = 'aws-newest-instance-role';

    /**
     * @var Ec2Client
     */
    private $ec2Client;

    public static function getSubscribedEvents()
    {
        return [
            ConsoleCommandEvent::class => [
                ['checkNewestInstance', 0],
            ],
        ];
    }

    public function __construct(Ec2Client $ec2Client)
    {
        $this->ec2Client = $ec2Client;
        $this->logger = new NullLogger();
    }

    /**
     * @return void
     */
    public function checkNewestInstance(ConsoleCommandEvent $consoleCommandEvent)
    {
        $role = $consoleCommandEvent
            ->getInput()
            ->getOption(NewestInstanceRoleListener::OPTION_AWS_NEWEST_INSTANCE_ROLE);

        if (!$role) {
            return;
        }

        try {
            $currentInstanceId = file_get_contents('http://169.254.169.254/latest/meta-data/instance-id');
        } catch (Throwable $throwable) {
            $this->disableCommand($consoleCommandEvent, 'Cannot reach 169.254.169.254');

            return;
        }

        if (!$currentInstanceId) {
            $this->disableCommand($consoleCommandEvent, 'Current instance id not found');

            return;
        }

        try {
            if ($currentInstanceId != $this->getNewestInstanceIdForRole($role)) {
                $this->disableCommand($consoleCommandEvent, 'Current instance is not the newest');

                return;
            }
        } catch (Throwable $throwable) {
            $this->disableCommand($consoleCommandEvent, $throwable->getMessage());

            return;
        }
    }

    private function disableCommand(ConsoleCommandEvent $event, $reason)
    {
        $event->disableCommand();
        $this->logger->info('Command disabled', ['reason' => $reason, 'service' => 'NewestInstanceRoleListener']);
    }

    private function getNewestInstanceIdForRole($role)
    {
        $result = $this->ec2Client->describeInstances([
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
        ]);

        $instances = [];
        foreach ($result['Reservations'] as $reservation) {
            foreach ($reservation['Instances'] as $instance) {
                $instances[(int) $instance['LaunchTime']->format('U')][] = $instance['InstanceId'];
            }
        }

        if (!$instances) {
            return null;
        }

        ksort($instances);

        $instanceIds = array_pop($instances);
        sort($instanceIds);

        return array_pop($instanceIds);
    }
}
