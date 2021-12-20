<?php

namespace Draw\Bundle\MessengerBundle\Broker;

use Draw\Bundle\MessengerBundle\Broker\Event\BrokerRunningEvent;
use Draw\Bundle\MessengerBundle\Broker\Event\BrokerStartedEvent;
use Draw\Bundle\MessengerBundle\Broker\Event\NewConsumerProcessEvent;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Broker
{
    private $eventDispatcher;

    private $stopped = false;

    private $allowFinishingProcess = true;

    private $symfonyConsolePath;

    public function __construct(
        string $symfonyConsolePath,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->symfonyConsolePath = $symfonyConsolePath;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function start(int $concurrent, int $timeout = 10): void
    {
        $this->eventDispatcher->dispatch(new BrokerStartedEvent($this, $concurrent, $timeout));

        $processes = [];
        while (true) {
            $this->eventDispatcher->dispatch(new BrokerRunningEvent($this));
            if (!$this->stopped) {
                $processes = array_merge(
                    $this->startProcesses($concurrent - \count($processes)),
                    $processes
                );
            }

            foreach ($processes as $key => $process) {
                if (!$process->isRunning()) {
                    unset($processes[$key]);
                }
            }
            sleep(1);

            if (!$this->stopped) {
                continue;
            }

            if (!$processes) {
                break;
            }

            if (!$this->allowFinishingProcess) {
                break;
            }
        }

        $this->stopProcesses($processes, $timeout);
    }

    public function stop(bool $allowFinishingProcess = true): void
    {
        $this->stopped = true;
        $this->allowFinishingProcess = $allowFinishingProcess;
    }

    /**
     * @param array|Process[] $processes
     */
    private function stopProcesses(array $processes, int $timeout)
    {
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                $process->signal(15); //15 is SIGTERM
            }
        }

        $timeoutMicro = microtime(true) + $timeout;

        // given SIGTERM may not be defined and that "proc_terminate" uses the constant value and not the constant itself, we use the same here
        do {
            usleep(1000);
            foreach ($processes as $key => $process) {
                if (!$process->isRunning()) {
                    unset($processes[$key]);
                }
            }
            if (!$processes) {
                break;
            }
        } while (microtime(true) < $timeoutMicro);

        foreach ($processes as $process) {
            $process->stop(0);
        }
    }

    /**
     * @return array|Process[]
     */
    private function startProcesses(int $amount): array
    {
        $processes = [];
        for ($i = 0; $i < $amount; ++$i) {
            $this->eventDispatcher->dispatch($event = new NewConsumerProcessEvent());

            if ($event->isStartPrevented()) {
                continue;
            }

            if (!$receivers = $event->getReceivers()) {
                throw new \RuntimeException(sprintf('You must have at least one receivers. If you do not want to prevent the consumer process to start use the [%s] event method.', NewConsumerProcessEvent::class.'::preventStart'));
            }

            $process = new Process(
                array_merge(
                    [$this->symfonyConsolePath, 'messenger:consume'],
                    $receivers,
                    $this->buildOptionsFromArray($event->getOptions())
                )
            );

            $process->setTimeout(null);
            $process->start();

            $processes[] = $process;
        }

        return $processes;
    }

    private function buildOptionsFromArray(array $options): array
    {
        $results = [];
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $oneValue) {
                    $results[] = '--'.$key;
                    $results[] = $oneValue;
                }
                continue;
            }

            $results[] = '--'.$key;

            if (null === $value) {
                continue;
            }

            $results[] = $value;
        }

        return $results;
    }
}
