<?php

namespace Draw\Bundle\SonataImportBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataExtraBundle\Notifier\Notification\SonataNotification;
use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Column\ColumnFactory;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Importer implements ImporterInterface
{
    public function __construct(
        /**
         * @var iterable<ColumnExtractorInterface>
         */
        #[TaggedIterator(ColumnExtractorInterface::class)]
        private iterable $columnsExtractors,
        private ManagerRegistry $managerRegistry,
        private ColumnFactory $columnFactory,
        private NotifierInterface $notifier,
    ) {
    }

    public function getOptions(Column $column): array
    {
        $options = [];

        foreach ($this->columnsExtractors as $mappedToOptionBuilder) {

            $options = $mappedToOptionBuilder->getOptions(
                $column,
                $options
            );
        }

        return $options;
    }

    public function buildFromFile(Import $import, \SplFileInfo $file): void
    {
        $import->setFileContent(file_get_contents($file->getRealPath()));

        $handle = fopen($file->getRealPath(), 'r');

        $headers = fgetcsv($handle);
        $samples = [];
        for ($i = 0; $i < 10; ++$i) {
            $row = fgetcsv($handle);
            if (!$row) {
                break;
            }
            $samples[] = $row;
        }

        $this->columnFactory->buildColumns(
            $import,
            $headers,
            $samples
        );

        $import->setState(Import::STATE_CONFIGURATION);
    }

    public function processImport(Import $import): bool
    {
        $file = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($file, $import->getFileContent());
        register_shutdown_function('unlink', $file);
        $handle = fopen($file, 'r+');
        $headers = fgetcsv($handle);

        $identifierHeaderName = $import->getIdentifierHeaderName();
        $columnMapping = $import->getColumnMapping();
        $line = 1;
        $saved = 0;

        $identifierColumns = $import->getIdentifierColumns();

        $identifierHeaderNames = [];
        foreach ($identifierColumns as $column) {
            $identifierHeaderNames[$column->getHeaderName()] = $column->getMappedTo();
        }

        while (($row = fgetcsv($handle)) !== false) {
            ++$line;
            $data = array_combine($headers, $row);
            $id = $data[$identifierHeaderName];
            $criteria = [];
            foreach ($identifierHeaderNames as $headerName => $mappedTo) {
                $criteria[$mappedTo] = $data[$headerName];
            }

            $model = $this->findOne($import->getEntityClass(), $criteria, $import->getInsertWhenNotFound());

            if (null === $model) {
                $this->notifier
                    ->send(
                        SonataNotification::error(
                            \sprintf(
                                'Skipped Id [%s] cannot be found at line [%s]. Make sure you are using unique id value.',
                                implode(', ', $criteria),
                                $line
                            )
                        )
                    );

                continue;
            }

            try {
                foreach ($columnMapping as $headerName => $column) {
                    $this->assignValue(
                        $model,
                        $column,
                        $data[$headerName]
                    );
                }
            } catch (\Throwable $exception) {
                $this->notifier
                    ->send(
                        SonataNotification::error(
                            \sprintf(
                                'Skipped Id [%s] at line [%s]. Error: %s',
                                $id,
                                $line,
                                $exception->getMessage()
                            )
                        )
                    );
                continue;
            }

            ++$saved;
        }

        try {
            $this->managerRegistry->getManagerForClass($import->getEntityClass())->flush();

            $this->notifier
                ->send(
                    SonataNotification::success(
                        \sprintf(
                            'Entity saved: %s',
                            $saved
                        )
                    )
                );

            return true;
        } catch (\Throwable $error) {
            $this->notifier
                ->send(
                    SonataNotification::error(
                        \sprintf(
                            'Error saving data: %s',
                            $error->getMessage()
                        )
                    )
                );

            return false;
        }
    }

    private function assignValue(object $object, Column $column, mixed $value): void
    {
        if ($column->getIsDate()) {
            $value = new \DateTime($value);
        }

        foreach ($this->columnsExtractors as $columnExtractor) {
            if ($columnExtractor->assign($object, $column, $value)) {
                return;
            }
        }
    }

    /**
     * The criteria can define path with dot for separator.
     *
     * @param array<string, string> $criteria
     */
    private function findOne(string $class, array $criteria, bool $create): ?object
    {
        $manager = $this->managerRegistry->getManagerForClass($class);

        \assert($manager instanceof EntityManagerInterface);

        $parameters = [];

        /** @var array<string,array<string>> $relationsCriteria */
        $relationsCriteria = [];

        foreach ($criteria as $key => $value) {
            if (1 === substr_count($key, '.')) {
                [$object, $field] = explode('.', $key);
                $relationsCriteria[$object][$field] = $value;
            } else {
                $parameters[$key] = $value;
            }
        }

        $classMetadata = $manager->getClassMetadata($class);

        foreach ($relationsCriteria as $relationName => $objectCriteria) {
            $objectClass = $classMetadata->getAssociationTargetClass($relationName);

            $relatedObject = $this->managerRegistry
                ->getRepository($objectClass)
                ->findOneBy($objectCriteria);

            if (!$relatedObject) {
                return null;
            }

            $parameters[$relationName] = $relatedObject;
        }

        $objects = $manager->getRepository($class)->findBy($parameters);

        if (\count($objects) > 1) {
            throw new NonUniqueResultException();
        }

        if (1 === \count($objects)) {
            return $objects[0];
        }

        if (!$create) {
            return null;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        $object = new $class();
        foreach ($parameters as $key => $value) {
            $accessor->setValue($object, $key, $value);
        }

        $manager->persist($object);

        return $object;
    }
}
