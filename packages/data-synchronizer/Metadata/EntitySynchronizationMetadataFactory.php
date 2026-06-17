<?php

declare(strict_types=1);

namespace Draw\Component\DataSynchronizer\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

class EntitySynchronizationMetadataFactory
{
    /**
     * @var array<string,?EntitySynchronizationMetadata>
     */
    private array $classConfigurations = [];

    /**
     * @var null|array<class-string>>
     */
    private ?array $classesToDump = null;

    public function __construct(
        private DumpingOrderCalculator $dumpingOrderCalculator,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @return array<EntitySynchronizationMetadata>
     */
    public function getAllEntitySynchronizationMetadata(): array
    {
        return array_map(
            fn (string $class) => $this->getEntitySynchronizationMetadata($class),
            $this->getClassesToDump()
        );
    }

    public function getEntitySynchronizationMetadata(string $className): ?EntitySynchronizationMetadata
    {
        if (!\array_key_exists($className, $this->classConfigurations)) {
            $this->classConfigurations[$className] = $this->loadEntitySynchronizationMetadata($className);
        }

        return $this->classConfigurations[$className];
    }

    private function loadFromAttribute(\ReflectionClass $reflectionClass): ?EntitySynchronizationMetadata
    {
        $attributes = $reflectionClass->getAttributes(EntitySynchronizationMetadata::class);

        return ($attributes[0] ?? null)?->newInstance() ?? null;
    }

    private function loadEntitySynchronizationMetadata(string $className): ?EntitySynchronizationMetadata
    {
        if (!class_exists($className)) {
            throw new \RuntimeException(\sprintf('Class "%s" does not exist', $className));
        }

        $reflectionClass = new \ReflectionClass($className);
        $attribute = $this->loadFromAttribute($reflectionClass);

        if ($attribute) {
            $classMetadata = $this->managerRegistry
                ->getManagerForClass($className)
                ->getMetadataFactory()
                ->getMetadataFor($className)
            ;

            if (!$classMetadata instanceof ClassMetadata) {
                throw new \RuntimeException(\sprintf('Class "%s" is not a Doctrine entity', $className));
            }

            $attribute->classMetadata = $classMetadata;

            return $attribute;
        }

        if ($parentClass = $reflectionClass->getParentClass()) {
            return $this->getEntitySynchronizationMetadata($parentClass->getName());
        }

        return null;
    }

    /**
     * @return array<class-string>
     */
    private function getClassesToDump(): array
    {
        if (null === $this->classesToDump) {
            $manager = $this->managerRegistry->getManager();
            $classes = [];
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metaData) {
                $attribute = $this->getEntitySynchronizationMetadata($metaData->getName());
                if (null === $attribute) {
                    continue;
                }

                $classes[] = $metaData->getName();
            }

            $classes = array_unique($classes);
            // Doctrine never give classes with the same order,
            // we need to make sure the order is the same for automation testing

            sort($classes);

            $classes = array_values($classes);

            $this->classesToDump = $this->dumpingOrderCalculator->getDumpOrder($classes);
        }

        return $this->classesToDump;
    }
}
