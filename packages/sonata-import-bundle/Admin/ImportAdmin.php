<?php

namespace Draw\Bundle\SonataImportBundle\Admin;

use Draw\Bundle\SonataImportBundle\Controller\ImportController;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Bundle\SonataImportBundle\Import\ImporterInterface;
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
        private ImporterInterface $importer,
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
            if ($this->importer->processImport($object)) {
                $object->setState(Import::STATE_PROCESSED);
                $this->getModelManager()->update($object);
            }
        }
    }

    private function processFileUpload(Import $import, ?UploadedFile $file = null): void
    {
        $flashBag = $this->getRequest()->getSession()->getFlashBag();

        if (null === $file) {
            $flashBag->add('sonata_flash_error', 'File not found.');

            return;
        }

        $this->importer
            ->buildFromFile($import, $file);
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
