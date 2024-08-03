<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Allow to iterate over a batch of object from the ProxyQueryInterface.
 *
 * @template T of object
 */
#[Autoconfigure(shared: false)]
class BatchIterator
{
    private ProxyQuery $query;

    private AdminInterface $admin;

    private string $action;

    private bool $checkObjectAccess = true;

    private bool $autoNotify = true;

    private int $processedCount = 0;

    /**
     * @var array<string, int>
     */
    private array $skippedCount = [
        'undefined' => 0,
        'insufficient-access' => 0,
    ];

    public function __construct(
        private BatchNotifier $batchNotifier
    ) {
    }

    public function initialize(ProxyQuery $query, AdminInterface $admin, string $action): self
    {
        $batchIterator = clone $this;

        $batchIterator->query = $query;
        $batchIterator->admin = $admin;
        $batchIterator->action = $action;

        return $batchIterator;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @return iterable<T>
     */
    public function getObjects(): iterable
    {
        $this->query->select('o.id as id');

        $this->admin->getModelManager();
        foreach ($this->query->execute() as $id) {
            $object = $this->admin->getObject($id['id']);

            ++$this->processedCount;

            if ($this->checkObjectAccess && !$this->admin->hasAccess($this->action, $object)) {
                $this->skip('insufficient-access');

                continue;
            }

            yield $object;
        }

        if ($this->autoNotify) {
            $this->notify();
        }
    }

    public function getAutoNotify(): bool
    {
        return $this->autoNotify;
    }

    public function setAutoNotify(bool $autoNotify): self
    {
        $this->autoNotify = $autoNotify;

        return $this;
    }

    public function notify(): void
    {
        $this->batchNotifier->notifyBatch($this);
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
}
