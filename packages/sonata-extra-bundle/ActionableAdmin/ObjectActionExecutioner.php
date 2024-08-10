<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\ExecutionErrorEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PostExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PreExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PrepareExecutionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow to execute action logic on an object or a batch of objects.
 *
 * @template T of object
 */
#[
    Autoconfigure(shared: false),
    AutoconfigureTag(
        'monolog.logger',
        attributes: [
            'channel' => 'sonata_admin',
        ]
    ),
    AutoconfigureTag(
        'logger.decorate',
        attributes: [
            'message' => '[ObjectActionExecutioner] {message}',
        ]
    )
]
class ObjectActionExecutioner
{
    private ?ProxyQuery $query = null;

    private ?object $subject = null;

    private AdminInterface $admin;

    private string $action;

    private bool $checkObjectAccess = true;

    private ?int $totalCount = null;

    private int $processedCount = 0;

    public array $options = [];

    /**
     * @var array<string, int>
     */
    private array $skippedCount = [
        'undefined' => 0,
        'insufficient-access' => 0,
    ];

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ?LoggerInterface $logger
    ) {
    }

    public function initialize(object $target, AdminInterface $admin, string $action): self
    {
        $batchIterator = clone $this;

        $batchIterator->query = $target instanceof ProxyQuery ? $target : null;
        $batchIterator->subject = !$target instanceof ProxyQuery ? $target : null;
        $batchIterator->totalCount = $batchIterator->subject ? 1 : null;
        $batchIterator->admin = $admin;
        $batchIterator->action = $action;

        return $batchIterator;
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
        if ($this->subject) {
            yield $this->subject;

            return;
        }

        $this->query->select('o.id as id');

        $this->admin->getModelManager();
        foreach ($this->query->execute() as $id) {
            $object = $this->admin->getObject($id['id']);

            yield $object;
        }
    }

    public function getCheckObjectAccess(): bool
    {
        return $this->checkObjectAccess;
    }

    public function setCheckObjectAccess(bool $checkObjectAccess): self
    {
        $this->checkObjectAccess = $checkObjectAccess;

        return $this;
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
        return null !== $this->query;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount ??= (clone $this->query)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSubject(): ?object
    {
        return $this->subject;
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

            if ($this->checkObjectAccess && !$this->admin->hasAccess($this->action, $object)) {
                $this->skip('insufficient-access');

                continue;
            }

            try {
                $response = $execution($object);
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
