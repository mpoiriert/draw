<?php

namespace Draw\Bundle\SonataImportBundle\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\ColumnFactory;
use Draw\Bundle\SonataImportBundle\Controller\ImportController;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Bundle\SonataImportBundle\Event\AttributeImportEvent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AutoconfigureTag(
    'sonata.admin',
    attributes: [
        'group' => 'Import',
        'model_class' => Import::class,
        'manager_type' => 'orm',
        'label' => 'Import',
        'controller' => ImportController::class,
        'icon' => '<i class="fa fa-upload"></i>',
    ]
)]
class ImportAdmin extends AbstractAdmin
{
    public function __construct(
        private ColumnFactory $columnFactory,
        private EventDispatcherInterface $eventDispatcher,
        private ManagerRegistry $managerRegistry,
        #[Autowire('%draw.sonata_import.classes%')]
        private array $importableClassList
    ) {
        parent::__construct();
    }

    protected function alterNewInstance(object $object): void
    {
        if ($entityClass = $this->getRequest()->query->get('entityClass')) {
            $object->setEntityClass($entityClass);
        }
    }

    /**
     * @param Import $object
     */
    protected function prePersist($object): void
    {
        /** @var UploadedFile $data */
        $data = $this->getForm()->get('file')->getData();
        $this->processFileUpload($object, $data);
    }

    /**
     * @param Import $object
     */
    protected function postUpdate($object): void
    {
        if (Import::STATE_VALIDATION === $object->getState()) {
            if ($this->processImport($object)) {
                $object->setState(Import::STATE_PROCESSED);
                $this->getModelManager()->update($object);
            }
        }
    }

    private function processImport(Import $import): bool
    {
        $flashBag = $this->getRequest()->getSession()->getFlashBag();

        $file = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($file, $import->getFileContent());
        register_shutdown_function('unlink', $file);
        $handle = fopen($file, 'r+');
        $headers = fgetcsv($handle);

        $identifierHeaderName = $import->getIdentifierHeaderName();
        $columnMapping = $import->getColumnMapping();
        $line = 1;
        $saved = 0;
        $accessor = PropertyAccess::createPropertyAccessor();

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
                $flashBag->add(
                    'sonata_flash_error',
                    'Skipped Id ['.implode(', ', $criteria).'] cannot be found at line ['.$line.']. Make sure you are using unique id value.'
                );
                continue;
            }

            try {
                foreach ($columnMapping as $headerName => $column) {
                    $value = $data[$headerName];
                    if ($column->getIsDate()) {
                        $value = new \DateTime($value);
                    }

                    $this->eventDispatcher->dispatch($event = new AttributeImportEvent($model, $column, $value));

                    if ($event->isPropagationStopped()) {
                        continue;
                    }

                    $accessor->setValue($model, $column->getMappedTo(), $value);
                }
            } catch (\Throwable $exception) {
                $flashBag->add(
                    'sonata_flash_error',
                    'Skipped Id ['.$id.'] at line ['.$line.']. Error: '.$exception->getMessage()
                );
                continue;
            }

            ++$saved;
        }

        try {
            $this->managerRegistry->getManagerForClass($import->getEntityClass())->flush();

            $flashBag->add(
                'sonata_flash_success',
                'Entity saved: '.$saved
            );

            return true;
        } catch (\Throwable $error) {
            $flashBag->add(
                'sonata_flash_error',
                'Error saving data:'.$error->getMessage()
            );

            return false;
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

    private function processFileUpload(Import $import, ?UploadedFile $file = null): void
    {
        $flashBag = $this->getRequest()->getSession()->getFlashBag();

        if (null === $file) {
            $flashBag->add('sonata_flash_error', 'File not found.');

            return;
        }

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

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add(
                'entityClass',
                'choice',
                [
                    'label' => 'Entity',
                    'choices' => array_flip($this->getEntityClassChoices()),
                ]
            )
            ->add('insertWhenNotFound')
            ->add('state')
            ->add('createdAt');
    }

    public function configureFormFields(FormMapper $form): void
    {
        /** @var Import $subject */
        $subject = $this->getSubject();
        $form
            ->ifTrue(Import::STATE_NEW === $subject->getState())
                ->add(
                    'entityClass',
                    ChoiceType::class,
                    [
                        'label' => 'Entity',
                        'choices' => $this->getEntityClassChoices(),
                    ]
                )
                ->add(
                    'file',
                    FileType::class,
                    [
                        'mapped' => false,
                        'help' => 'CSV File with column headers.',
                    ]
                )
            ->ifEnd()
            ->ifTrue(\in_array($subject->getState(), [Import::STATE_CONFIGURATION, Import::STATE_VALIDATION], true))
                ->add('insertWhenNotFound')
                ->add(
                    'columns',
                    CollectionType::class,
                    [
                        'by_reference' => false,
                        'btn_add' => false,
                        'type_options' => [
                            'btn_delete' => false,
                        ],
                    ],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ]
                )
                ->add(
                    'state',
                    ChoiceType::class,
                    [
                        'label' => 'Process',
                        'help' => 'If you want to process the file',
                        'choices' => [
                            'No' => Import::STATE_CONFIGURATION,
                            'Yes' => Import::STATE_VALIDATION,
                        ],
                    ]
                )
            ->ifEnd()
            ->ifTrue(Import::STATE_PROCESSED === $subject->getState())
                ->add(
                    'insertWhenNotFound',
                    null,
                    [
                        'attr' => ['disabled' => true],
                    ]
                )
                ->add(
                    'state',
                    null,
                    [
                        'attr' => ['disabled' => true],
                    ]
                )
            ->ifEnd();
    }

    private function getEntityClassChoices(): array
    {
        $choices = [];
        foreach ($this->importableClassList as $configuration) {
            $label = $configuration['alias'] ?? $configuration['name'];
            $choices[$label] = $configuration['name'];
        }

        return $choices;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('download', $this->getRouterIdParameter().'/download');
    }

    public function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $buttonList = parent::configureActionButtons($buttonList, $action, $object);
        if (\in_array($action, ['edit', 'show'], true)) {
            $buttonList['download'] = [
                'template' => '@DrawSonataImport\ImportAdmin\button_download.html.twig',
            ];
        }

        return $buttonList;
    }
}
