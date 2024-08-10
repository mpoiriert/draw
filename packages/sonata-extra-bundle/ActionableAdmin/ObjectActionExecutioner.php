<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\ExecutionErrorEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\ExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PostExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PreExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PrepareExecutionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow to execute action logic on an object or a batch of objects.
 *
 * @template T of object
 */
#[Exclude]
class ObjectActionExecutioner
{
    private ?int $totalCount;

    private int $processedCount = 0;

    public array $options = [];

    /**
     * @var array<string, int>
     */
    private array $skippedCount = [];

    public function __construct(
        private AdminInterface $admin,
        private string $action,
        private object $target,
        private EventDispatcherInterface $eventDispatcher,
        private ?LoggerInterface $logger
    ) {
        $this->totalCount = !($this->target instanceof ProxyQuery) ? 1 : null;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @return iterable<T>
     */
    private function getObjects(): iterable
    {
        if (!$this->target instanceof ProxyQuery) {
            yield $this->target;

            return;
        }

        $this->target->select('o.id as id');

        $this->admin->getModelManager();
        foreach ($this->target->execute() as $id) {
            $object = $this->admin->getObject($id['id']);

            yield $object;
        }
    }

    public function skip(string $reason = 'undefined'): void
    {
        --$this->processedCount;

        $this->skippedCount[$reason] = ($this->skippedCount[$reason] ?? 0) + 1;
    }

    /**
     * @return array<string, int>
     */
    public function getSkippedCount(): array
    {
        return $this->skippedCount;
    }

    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    public function isBatch(): bool
    {
        return $this->target instanceof ProxyQuery;
    }

    public function getTotalCount(): int
    {
        // Total count is initialized to 1 in constructor if the target is not a ProxyQuery.
        if (null === $this->totalCount) {
            \assert($this->target instanceof ProxyQuery);
            $this->totalCount = (clone $this->target)
                ->select('COUNT(o.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->totalCount;
    }

    /**
     * @return T
     */
    public function getSubject(): ?object
    {
        return $this->target instanceof ProxyQuery ? null : $this->target;
    }

    /**
     * If preExecution return a response, the execution will be stopped and the response will be returned immediately.
     *
     * @param callable|array{
     *     preExecution?: callable(): ?Response,
     *     execution: callable,
     *     postExecution?: callable(): ?Response,
     *     onExecutionError?: callable(ExecutionErrorEvent): void
     * } $executions
     */
    public function execute(
        array|callable $executions
    ): ?Response {
        if (\is_callable($executions)) {
            $executions = ['execution' => $executions];
        }

        $preExecution = $executions['preExecution'] ?? null;
        $execution = $executions['execution'] ?? null;
        $postExecution = $executions['postExecution'] ?? null;
        $onExecutionError = $executions['onExecutionError'] ?? null;

        if (!\is_callable($execution)) {
            throw new \InvalidArgumentException('The execution is not callable.');
        }

        $this->eventDispatcher->dispatch(new PrepareExecutionEvent($this));

        $response = $preExecution ? $preExecution() : null;

        if (!$response instanceof Response) {
            $this->eventDispatcher->dispatch($event = new PreExecutionEvent($this));

            $response = $event->getResponse();
        }

        if ($response instanceof Response) {
            return $response;
        }

        foreach ($this->getObjects() as $object) {
            ++$this->processedCount;

            try {
                $this->eventDispatcher->dispatch($event = new ExecutionEvent($object, $this));

                $response = $event->getResponse();

                if ($event->isPropagationStopped()) {
                    continue;
                }

                $response = $execution($object) ?? $response;
            } catch (\Throwable $error) {
                $this->logger?->error(
                    'An error occurred during the execution of {action}.',
                    [
                        'action' => $this->action,
                        'error' => $error,
                        'objectId' => $this->admin->id($object),
                        'object' => $object::class,
                    ]
                );
                $this->skip('error');

                $event = new ExecutionErrorEvent($error, $object, $this);

                $onExecutionError && $onExecutionError($event);

                if (!$event->isPropagationStopped()) {
                    $this->eventDispatcher->dispatch($event);
                }

                if ($event->getStopExecution()) {
                    $response = $event->getResponse();

                    break;
                }
            }

            if ($response instanceof Response) {
                break;
            }
        }

        $response = ($postExecution ? $postExecution() : null) ?? $response;

        $this->eventDispatcher->dispatch($event = new PostExecutionEvent($this, $response));

        return $event->getResponse();
    }
}
