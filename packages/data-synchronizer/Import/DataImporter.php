<?php

namespace Draw\Component\DataSynchronizer\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DataSynchronizer\Event\DataImportCompletedEvent;
use Draw\Component\DataSynchronizer\Event\PreDeleteEntityEvent;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DataImporter
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        #[Autowire(service: 'draw.data_synchronizer.serializer')]
        private Serializer $serializer,
        private EventDispatcherInterface $eventDispatcher,
        private ImportationContextFactory $contextImportPreparator,
    ) {
    }

    public function import(string $file): void
    {
        $importationContext = $this->contextImportPreparator->create($file);

        $this->doRestore($importationContext->getRestorationData());

        $this->doRestore($importationContext->getLateRestorationData());

        $this->deleteEntities(array_reverse($importationContext->getToDelete()));

        $this->eventDispatcher->dispatch(new DataImportCompletedEvent());
    }

    /**
     * @param array<RestorationData> $restorations
     */
    private function doRestore(array $restorations): void
    {
        $manager = $this->getEntityManager();

        foreach ($restorations as $restorationData) {
            $class = $restorationData->getClass();

            $count = 0;
            foreach ($restorationData->getData() as $entity) {
                $manager->persist($this->loadEntity($entity, $class));

                ++$count;
                if ($count >= 1000) {
                    $manager->flush();
                    $count = 0;
                }
            }

            if ($count > 0) {
                $manager->flush();
            }

            $manager->clear();
        }
    }

    /**
     * @param array<RestorationData> $toDelete
     */
    private function deleteEntities(array $toDelete): void
    {
        $manager = $this->getEntityManager();

        $unitOfWork = $manager->getUnitOfWork();

        foreach ($toDelete as $restorationData) {
            $count = 0;
            foreach ($restorationData->getData() as $entityData) {
                $entity = $this->loadEntity(
                    $entityData,
                    $restorationData->getClass(),
                );

                if (UnitOfWork::STATE_MANAGED !== $unitOfWork->getEntityState($entity)) {
                    continue;
                }

                $this->eventDispatcher->dispatch($event = new PreDeleteEntityEvent($entity));

                if (!$event->isAllowDelete()) {
                    continue;
                }

                $manager->remove($entity);

                ++$count;

                if ($count >= 1000) {
                    $manager->flush();
                    $count = 0;
                }
            }

            if ($count > 0) {
                $manager->flush();
            }

            $manager->clear();
        }
    }

    private function loadEntity(array $data, string $type): object
    {
        return $this->serializer->fromArray(
            $data,
            $type,
            DeserializationContext::create()->setGroups('Default')
        );
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $manager = $this->managerRegistry->getManager();

        if (!$manager instanceof EntityManagerInterface) {
            throw new \RuntimeException('No entity manager found');
        }

        return $manager;
    }
}
