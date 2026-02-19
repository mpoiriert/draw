<?php

namespace Draw\Component\DataSynchronizer\Import;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class ImportationContext
{
    /**
     * @var array<RestorationData>
     */
    private array $lateRestorationData = [];

    /**
     * @var array<RestorationData>
     */
    private array $restorationData = [];

    /**
     * @var array<RestorationData>
     */
    private array $toDelete = [];

    public function addRestorationData(RestorationData $restorationData): void
    {
        $this->restorationData[] = $restorationData;
    }

    /**
     * @return array<RestorationData>
     */
    public function getRestorationData(): array
    {
        return $this->restorationData;
    }

    public function addLateRestorationData(RestorationData $restorationData): void
    {
        $this->lateRestorationData[] = $restorationData;
    }

    /**
     * @return array<RestorationData>
     */
    public function getLateRestorationData(): array
    {
        return $this->lateRestorationData;
    }

    public function addToDelete(RestorationData $restorationData): void
    {
        $this->toDelete[] = $restorationData;
    }

    /**
     * @return array<RestorationData>
     */
    public function getToDelete(): array
    {
        return $this->toDelete;
    }
}
