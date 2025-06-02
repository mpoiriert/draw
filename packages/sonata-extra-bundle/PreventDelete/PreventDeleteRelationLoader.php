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
        private ?string $cacheDirectory = null,
    ) {
    }

    /**
     * @return array<PreventDelete>
     */
    public function getRelationsForObject(object $object): array
    {
        $relations = [];
        foreach ($this->getRelations() as $relation) {
            $class = $relation->getClass();
            if ($object instanceof $class) {
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
        $metadatas = [];
        $associationIdentifiers = [];

        foreach ($managerRegistry->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                if (!$metadata instanceof ClassMetadata) {
                    continue;
                }

                $metadatas[] = $metadata;

                foreach ($metadata->associationMappings as $key => $associationMapping) {
                    $associationIdentifiers[self::getAssociationIdentifier($metadata->getName(), $key)] = true;
                }
            }
        }

        $relations = [];

        foreach ($metadatas as $metadata) {
            if ($metadata->isMappedSuperclass) {
                continue;
            }

            $relations = array_merge(
                $relations,
                $this->preventDeleteFromClassAttributes($metadata->getReflectionClass())
            );

            foreach ($metadata->associationMappings as $associationMapping) {
                // We want foreign key only
                if (!$associationMapping['isOwningSide']) {
                    continue;
                }

                $parentClass = $associationMapping['inherited'] ?? null;

                // Associations defined in parent classes will be taken into account
                if (
                    null !== $parentClass
                    && \array_key_exists(
                        self::getAssociationIdentifier($parentClass, $associationMapping['fieldName']),
                        $associationIdentifiers
                    )
                ) {
                    continue;
                }

                $preventDeleteFromAttribute = $this->preventDeleteFromPropertyAttribute($associationMapping);

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

        return $relations;
    }

    private function preventDeleteFromPropertyAttribute(array $associationMapping): ?PreventDelete
    {
        try {
            $attributes = (new \ReflectionProperty(
                $associationMapping['sourceEntity'],
                $associationMapping['fieldName']
            ))
                ->getAttributes(PreventDelete::class, \ReflectionAttribute::IS_INSTANCEOF)
            ;

            if (\count($attributes) > 1) {
                throw new \LogicException('Only one PreventDelete attribute is allowed per property. Repeatable is only allowed on class.');
            }

            return ($attributes[0] ?? null)?->newInstance();
        } catch (\ReflectionException) {
            return null;
        }
    }

    /**
     * @return array<PreventDelete>
     */
    private function preventDeleteFromClassAttributes(\ReflectionClass $reflectionClass): array
    {
        $reflectionClassName = $reflectionClass->getName();

        $attributes = array_merge(
            $reflectionClass->getAttributes(PreventDelete::class, \ReflectionAttribute::IS_INSTANCEOF),
            $this->getPreventDeleteAttributesFromTrait($reflectionClass)
        );

        $preventDeletes = [];

        foreach ($attributes as $attribute) {
            $preventDelete = $attribute->newInstance();
            \assert($preventDelete instanceof PreventDelete);

            if (!$preventDelete->getPreventDelete()) {
                continue;
            }

            if (null === $preventDelete->getClass()) {
                $preventDelete->setClass($reflectionClassName);
            }

            if (null === $preventDelete->getRelatedClass()) {
                $preventDelete->setRelatedClass($reflectionClassName);
            }

            $preventDeletes[] = $preventDelete;
        }

        return $preventDeletes;
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

    /**
     * @return array<\ReflectionAttribute>
     */
    private function getPreventDeleteAttributesFromTrait(\ReflectionClass $reflectionClass): array
    {
        $attributes = [];

        foreach ($reflectionClass->getTraits() as $reflectionTraitClass) {
            $attributes = array_merge(
                $attributes,
                $reflectionTraitClass->getAttributes(PreventDelete::class, \ReflectionAttribute::IS_INSTANCEOF),
                $this->getPreventDeleteAttributesFromTrait($reflectionTraitClass)
            );
        }

        return $attributes;
    }

    private static function getAssociationIdentifier(string $class, string $associationName): string
    {
        return \sprintf('%s::%s', $class, $associationName);
    }
}
