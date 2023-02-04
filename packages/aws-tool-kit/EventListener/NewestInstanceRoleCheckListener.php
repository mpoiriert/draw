<?php

namespace Draw\Component\AwsToolKit\EventListener;

use Aws\Ec2\Ec2Client;
use Draw\Component\AwsToolKit\Imds\ImdsClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This command listener is use to check if a command can be executed or not base on the fact it's the newest
 * instance in a specific role. This is useful when you want a cron to be executed only on one running instance in a pool
 * of instance and that all your server have the same cron configuration. This is check every time at runtime.
 *
 * Example:
 *   console/bin acme:purge-database --aws-newest-instance-role=prod
 */
class NewestInstanceRoleCheckListener implements EventSubscriberInterface
{
    final public const OPTION_AWS_NEWEST_INSTANCE_ROLE = 'aws-newest-instance-role';

    private LoggerInterface $logger;

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => [
                ['checkNewestInstance', 50],
            ],
        ];
    }

    public function __construct(
        private Ec2Client $ec2Client,
        private ImdsClientInterface $imdsClient,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    public function checkNewestInstance(ConsoleCommandEvent $consoleCommandEvent): void
    {
        $input = $consoleCommandEvent->getInput();

        if (!$input->hasOption(static::OPTION_AWS_NEWEST_INSTANCE_ROLE)) {
            return;
        }

        $role = (string) $input->getOption(static::OPTION_AWS_NEWEST_INSTANCE_ROLE);

        if (!$role) {
            return;
        }

        try {
            $currentInstanceId = $this->imdsClient->getCurrentInstanceId();
        } catch (\Throwable $throwable) {
            $this->disableCommand($consoleCommandEvent, 'Cannot reach 169.254.169.254');

            return;
        }

        if (!$currentInstanceId) {
            $this->disableCommand($consoleCommandEvent, 'Current instance id not found');

            return;
        }

        try {
            if ($currentInstanceId !== $this->getNewestInstanceIdForRole($role)) {
                $this->disableCommand($consoleCommandEvent, 'Current instance is not the newest');

                return;
            }
        } catch (\Throwable $throwable) {
            $this->disableCommand($consoleCommandEvent, $throwable->getMessage());

            return;
        }
    }

    private function disableCommand(ConsoleCommandEvent $event, string $reason): void
    {
        $event->disableCommand();
        $this->logger->info('Command disabled', ['reason' => $reason, 'service' => 'NewestInstanceRoleListener']);
    }

    /**
     * @internal
     */
    public function getNewestInstanceIdForRole(string $role): ?string
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
