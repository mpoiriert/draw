<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\ConfigCache;

class PreventDeleteRelationLoader
{
    private ?array $relations = null;

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private array $configuration,
        private bool $useManager = true,
        private bool $preventDeleteFromAllRelations = false,
        private ?string $cacheDirectory = null
    ) {
    }

    /**
     * @return array<PreventDelete>
     */
    public function getRelationsForObject(object $object): array
    {
        $relations = [];
        foreach ($this->getRelations() as $relation) {
            if ($relation->getClass() === $object::class) {
                $relations[] = $relation;
            }
        }

        return $relations;
    }

    /**
     * @return array<PreventDelete>
     */
    public function getRelations(): array
    {
        if (null === $this->relations) {
            if (null === $this->cacheDirectory) {
                return $this->relations = $this->loadRelations();
            }

            $path = $this->cacheDirectory.'/draw-sonata-extra-prevent-cache.php';

            $cache = new ConfigCache($path, false);

            if (!$cache->isFresh()) {
                $relations = $this->loadRelations();

                $cache->write(
                    '<?php return unserialize('.var_export(serialize($relations), true).');',
                    []
                );

                return $this->relations = $relations;
            }

            return $this->relations = require $path;
        }

        return $this->relations;
    }

    /**
     * @return array<PreventDelete>
     */
    private function loadRelations(): array
    {
        $relations = $this->getRelationsFromManager($this->managerRegistry);

        $config = $this->configuration;

        foreach ($config as $entity) {
            if (false === $entity['prevent_delete']) {
                foreach ($relations as $index => $relation) {
                    if ($relation->getClass() === $entity['class']) {
                        unset($relations[$index]);
                    }
                }
            }

            foreach ($entity['relations'] as $relation) {
                $index = $this->searchRelation(
                    $relations,
                    $entity['class'],
                    $relation['related_class'],
                    $relation['path']
                );

                if (false === $relation['prevent_delete']) {
                    if (null !== $index) {
                        unset($relations[$index]);
                    }
                    continue;
                }

                $metadata = null;
                if (null !== $index) {
                    $metadata = $relations[$index]->getMetadata();
                    unset($relations[$index]);
                }

                $relations[] = new PreventDelete(
                    $entity['class'],
                    $relation['related_class'],
                    $relation['path'],
                    metadata: [
                        ...$metadata ?? [],
                        ...$relation['metadata'] ?? [],
                    ]
                );
            }
        }

        return array_values($relations);
    }

    /**
     * @param array<PreventDelete> $preventDeletions
     */
    private function searchRelation(array $preventDeletions, string $class, string $relatedClass, string $path): ?int
    {
        foreach ($preventDeletions as $index => $preventDeletion) {
            if (
                $preventDeletion->getClass() === $class
                && $preventDeletion->getRelatedClass() === $relatedClass
                && $preventDeletion->getPath() === $path
            ) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return array<PreventDelete>
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

                    $preventDeleteFromAttribute = $this->preventDeleteFromAttribute($associationMapping);

                    // Not preventing delete from attribute as precedence over preventing delete from association
                    if ($preventDeleteFromAttribute && !$preventDeleteFromAttribute->getPreventDelete()) {
                        continue;
                    }

                    if (
                        !$this->preventDeleteFromAssociation($associationMapping)
                        && null === $preventDeleteFromAttribute
                    ) {
                        continue;
                    }

                    $relations[] = new PreventDelete(
                        $associationMapping['targetEntity'],
                        $metadata->getName(),
                        $associationMapping['fieldName'],
                        metadata: $preventDeleteFromAttribute?->getMetadata() ?? []
                    );
                }
            }
        }

        return $relations;
    }

    private function preventDeleteFromAttribute(array $associationMapping): ?PreventDelete
    {
        try {
            $attribute = (new \ReflectionProperty($associationMapping['sourceEntity'], $associationMapping['fieldName']))
                    ->getAttributes(PreventDelete::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

            return $attribute?->newInstance();
        } catch (\ReflectionException) {
            return null;
        }
    }

    private function preventDeleteFromAssociation(array $associationMapping): bool
    {
        if (!$this->useManager) {
            return false;
        }

        if ($this->preventDeleteFromAllRelations) {
            return true;
        }

        if ($associationMapping['isOnDeleteCascade'] ?? false) {
            return false;
        }

        foreach ($associationMapping['joinColumns'] ?? [] as $joinColumn) {
            if (!isset($joinColumn['onDelete'])) {
                continue;
            }

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
