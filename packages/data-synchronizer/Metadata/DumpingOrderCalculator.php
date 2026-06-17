<?php

namespace Draw\Component\DataSynchronizer\Metadata;

use Doctrine\ORM\Internal\TopologicalSort;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

class DumpingOrderCalculator
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @return array<class-string>
     */
    public function getDumpOrder(array $classNames): array
    {
        $entityManager = $this->managerRegistry->getManager();

        $sort = new TopologicalSort();

        // See if there are any new classes in the changeset, that are not in the
        // sorter graph yet (don't have a node).
        // We have to inspect changeSet to be able to correctly build dependencies.
        // It is not possible to use IdentityMap here because post inserted ids
        // are not yet available.
        $newNodes = [];

        foreach ($classNames as $className) {
            if ($sort->hasNode($classMetadata = $entityManager->getClassMetadata($className))) {
                continue;
            }

            $sort->addNode($classMetadata);

            $newNodes[] = $classMetadata;
        }

        // Calculate dependencies for new nodes
        while ($classMetadata = array_pop($newNodes)) {
            \assert($classMetadata instanceof ClassMetadata);
            foreach ($classMetadata->associationMappings as $association) {
                if (!$association['isOwningSide']) {
                    continue;
                }

                $targetClassMetadata = $entityManager->getClassMetadata($association['targetEntity']);

                \assert($targetClassMetadata instanceof ClassMetadata);

                // All classes, which we are going to export, have been already added. If target class of association
                // hasn't been added to the sorter, we don't export it and skip adding a dependency.
                if (!$sort->hasNode($targetClassMetadata)) {
                    continue;
                }

                $joinColumns = $association['joinColumns'] ?? [];
                $joinColumns = reset($joinColumns);

                $sort->addEdge(
                    $targetClassMetadata,
                    $classMetadata,
                    $isEdgeOptional = !isset($joinColumns['nullable']) || $joinColumns['nullable']
                );

                // If the target class has mapped subclasses, these share the same dependency.
                if (!$targetClassMetadata->subClasses) {
                    continue;
                }

                foreach ($targetClassMetadata->subClasses as $subClassName) {
                    if (!$sort->hasNode($targetSubClass = $entityManager->getClassMetadata($subClassName))) {
                        $sort->addNode($targetSubClass);

                        $newNodes[] = $targetSubClass;
                    }

                    $sort->addEdge(
                        $targetSubClass,
                        $classMetadata,
                        $isEdgeOptional
                    );
                }
            }
        }

        $results = array_map(
            static fn (ClassMetadata $classMetadata) => $classMetadata->name,
            $sort->sort()
        );

        return array_reverse($results);
    }
}
