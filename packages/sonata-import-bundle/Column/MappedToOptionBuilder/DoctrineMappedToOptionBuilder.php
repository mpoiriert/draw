<?php

namespace Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Event\AttributeImportEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DoctrineMappedToOptionBuilder implements MappedToOptionBuilderInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[AsEventListener]
    public function handleAttributeImportEvent(AttributeImportEvent $event): void
    {
        $column = $event->getColumn();

        if (!str_contains($column->getMappedTo(), '.')) {
            return;
        }

        $class = $column->getImport()->getEntityClass();

        $metadata = $this->managerRegistry->getManagerForClass($class)->getClassMetadata($class);

        [$relation, $field] = explode('.', $column->getMappedTo());

        if (!isset($metadata->associationMappings[$relation])) {
            return;
        }

        $targetEntityClass = $metadata->associationMappings[$relation]['targetEntity'];

        $targetEntity = $this->managerRegistry->getRepository($targetEntityClass)->findOneBy([$field => $event->getValue()]);

        if (null === $targetEntity) {
            return;
        }

        $event->getEntity()->{'set'.ucfirst($relation)}($targetEntity);

        $event->stopPropagation();
    }

    public function getOptions(Column $column, array $options): array
    {
        $class = $column->getImport()->getEntityClass();

        $metadata = $this->managerRegistry->getManagerForClass($class)->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return $options;
        }

        foreach ($metadata->fieldNames as $fieldName) {
            $options[] = $fieldName;
        }

        foreach ($metadata->associationMappings as $name => $associationMapping) {
            if (!($associationMapping['type'] & ClassMetadataInfo::TO_ONE)) {
                continue;
            }

            $targetClassMetadata = $this->managerRegistry
                ->getManagerForClass($associationMapping['targetEntity'])
                ->getClassMetadata($associationMapping['targetEntity']);

            if (!$targetClassMetadata instanceof ClassMetadata) {
                continue;
            }

            foreach ($targetClassMetadata->fieldMappings as $fieldName => $fieldMapping) {
                if (!($fieldMapping['id'] ?? false) && !($fieldMapping['unique'] ?? false)) {
                    continue;
                }

                $options[] = $name.'.'.$fieldName;
            }
        }

        return $options;
    }
}
