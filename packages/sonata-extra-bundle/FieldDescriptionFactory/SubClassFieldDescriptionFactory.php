<?php

namespace Draw\Bundle\SonataExtraBundle\FieldDescriptionFactory;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

class SubClassFieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    public function __construct(
        private FieldDescriptionFactoryInterface $decorated,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        if (!isset($options['forClasses'])) {
            $clasMetadata = $this->managerRegistry->getManagerForClass($class)->getClassMetadata($class);
            if (
                $clasMetadata instanceof ClassMetadata
                && ClassMetadata::INHERITANCE_TYPE_NONE !== $clasMetadata->inheritanceType
            ) {
                if ($forClasses = $this->getForClasses($clasMetadata, $name)) {
                    $options['forClasses'] = $forClasses;
                }
            }
        }

        return $this->decorated
            ->create($options['forClasses'][0] ?? $class, $name, $options)
        ;
    }

    private function getForClasses(ClassMetadata $classMetadata, string $fieldName): ?array
    {
        $fieldName = explode('.', $fieldName)[0];

        if ($this->isPropertyAvailable($classMetadata, $fieldName)) {
            return null;
        }

        $forClasses = [];
        foreach ($classMetadata->subClasses as $subClass) {
            $subClassMetadata = $this->managerRegistry
                ->getManagerForClass($subClass)
                ->getClassMetadata($subClass)
            ;

            \assert($subClassMetadata instanceof ClassMetadata);

            if ($this->isPropertyAvailable($subClassMetadata, $fieldName)) {
                $forClasses[] = $subClassMetadata->getName();
            }
        }

        return $forClasses ?: null;
    }

    private function isPropertyAvailable(ClassMetadata $classMetadata, string $property): bool
    {
        return $classMetadata->hasField($property)
            || $classMetadata->hasAssociation($property);
    }
}
