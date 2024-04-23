<?php

namespace Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Event\AttributeImportEvent;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DoctrineTranslationMappedToOptionBuilder implements MappedToOptionBuilderInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private array $supportedLocales = []
    ) {
    }

    #[AsEventListener]
    public function handleAttributeImportEvent(AttributeImportEvent $event): void
    {
        $column = $event->getColumn();

        if (!str_starts_with($column->getMappedTo(), 'translation#')) {
            return;
        }

        $model = $event->getEntity();

        if (!$model instanceof TranslatableInterface) {
            return;
        }

        $fieldInfo = explode('#', $column->getMappedTo())[1];

        [$locale, $fieldName] = explode('.', $fieldInfo);

        $model->translate($locale, false)->{'set'.ucfirst($fieldName)}($event->getValue());

        $event->stopPropagation();
    }

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
            ->getClassMetadata($translationClass);

        if (!$metadata instanceof ClassMetadata) {
            return $options;
        }

        foreach ($metadata->fieldMappings as $field => $fieldMapping) {
            if ($fieldMapping['id'] ?? false) {
                continue;
            }

            if ('locale' === $field) {
                continue;
            }

            foreach ($this->supportedLocales as $locale) {
                $options[] = sprintf('translation#%s.%s', $locale, $field);
            }
        }

        return $options;
    }
}
