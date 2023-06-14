<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

class PreventDeleteRelationLoader
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private array $configuration
    ) {
    }

    /**
     * @return iterable<PreventDelete>
     */
    public function getRelations(): iterable
    {
        $relations = $this->getRelationsFromManager($this->managerRegistry);

        $config = $this->configuration;

        foreach ($config as $entity) {
            foreach ($entity['relations'] as $relation) {
                $key = $relation['related_class'].'.'.$relation['path'];

                if (false === $entity['prevent_delete'] && false === $relation['prevent_delete']) {
                    unset($relations[$key]);
                    continue;
                }

                if (isset($relations[$key])) {
                    continue;
                }

                $relations[] = new PreventDelete(
                    $entity['class'],
                    $relation['related_class'],
                    $relation['path'],
                );
            }
        }

        return $relations;
    }

    /**
     * @return array<string, PreventDelete>
     */
    private function getRelationsFromManager(ManagerRegistry $managerRegistry): array
    {
        $relations = [];
        foreach ($managerRegistry->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                if (!$metadata instanceof ClassMetadata) {
                    continue;
                }

                foreach ($metadata->associationMappings as $associationMapping) {
                    // We want foreign key only
                    if (!$associationMapping['isOwningSide']) {
                        continue;
                    }

                    if (
                        !$this->preventDelete($associationMapping)
                        && !$this->preventDeleteFromAttribute($associationMapping)
                    ) {
                        continue;
                    }

                    $key = $metadata->getName().'.'.$associationMapping['fieldName'];
                    $relations[$key] = new PreventDelete(
                        $associationMapping['targetEntity'],
                        $metadata->getName(),
                        $associationMapping['fieldName'],
                    );
                }
            }
        }

        return $relations;
    }

    private function preventDeleteFromAttribute(array $associationMapping): bool
    {
        try {
            return (bool) \count(
                (new \ReflectionProperty($associationMapping['sourceEntity'], $associationMapping['fieldName']))
                        ->getAttributes(PreventDelete::class, \ReflectionAttribute::IS_INSTANCEOF)
            );
        } catch (\ReflectionException) {
            return false;
        }
    }

    private function preventDelete(array $associationMapping): bool
    {
        if ($associationMapping['isOnDeleteCascade'] ?? false) {
            return false;
        }

        foreach ($associationMapping['joinColumns'] ?? [] as $joinColumn) {
            if ('SET NULL' === $joinColumn['onDelete']) {
                return false;
            }

            if ('CASCADE' === $joinColumn['onDelete']) {
                return false;
            }
        }

        return true;
    }
}
