<?php

namespace Draw\Bundle\SonataImportBundle\Column\Bridge\KnpDoctrineBehaviors\Extractor;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

class DoctrineTranslationColumnExtractor extends BaseColumnExtractor
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private array $supportedLocales = [],
    ) {
    }

    #[\Override]
    public function getOptions(Column $column, array $options): array
    {
        $class = $column->getImport()->getEntityClass();

        $metadata = $this->managerRegistry->getManagerForClass($class)->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return $options;
        }

        $reflectionClass = $metadata->getReflectionClass();

        if (!$reflectionClass->implementsInterface(TranslatableInterface::class)) {
            return $options;
        }

        $translationClass = $reflectionClass->getMethod('getTranslationEntityClass')->invoke(null);

        $metadata = $this->managerRegistry
            ->getManagerForClass($translationClass)
            ->getClassMetadata($translationClass)
        ;

        if (!$metadata instanceof ClassMetadata) {
            return $options;
        }

        foreach ($metadata->fieldMappings as $field => $fieldMapping) {
            if ($metadata->isIdentifier($field)) {
                continue;
            }

            if ('locale' === $field) {
                continue;
            }

            foreach ($this->supportedLocales as $locale) {
                $options[] = \sprintf('translation#%s.%s', $locale, $field);
            }
        }

        return $options;
    }

    #[\Override]
    public function assign(object $object, Column $column, mixed $value): bool
    {
        if (!\in_array($column->getMappedTo(), $this->getOptions($column, []), true)) {
            return false;
        }

        if (!$object instanceof TranslatableInterface) {
            return false;
        }

        $fieldInfo = explode('#', $column->getMappedTo())[1];

        [$locale, $fieldName] = explode('.', $fieldInfo);

        $object->translate($locale, false)->{'set'.ucfirst($fieldName)}($value);

        return true;
    }
}
