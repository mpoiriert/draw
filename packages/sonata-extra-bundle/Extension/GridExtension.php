<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Show\ShowMapper;

class GridExtension extends AbstractAdminExtension
{
    private $guesser;

    public function __construct(TypeGuesserInterface $guesser)
    {
        $this->guesser = $guesser;
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $admin = $show->getAdmin();

        foreach ($show->keys() as $key) {
            $field = $show->get($key);
            if ('grid' !== $field->getType()) {
                continue;
            }

            $this->configureGrid($field, $admin);
        }
    }

    private function configureGrid(FieldDescriptionInterface $field, AdminInterface $admin): void
    {
        $fields = [];
        $field->setTemplate('@DrawSonataExtra/CRUD/show_grid.html.twig');
        $fieldAdmin = $field->getOption('fieldsAdmin');
        foreach ($field->getOption('fields', []) as $key => $options) {
            $fieldName = $options['fieldName'] ?? ($fieldAdmin ? $key : $field->getName().'.'.$key);
            $fieldAdmin = $options['admin'] ?? $fieldAdmin ?? $admin;
            if (isset($options['type'])) {
                $options['options']['fieldValueOnly'] = true;
                $fields[$key] = $this->newFieldDescriptionInstance(
                    $fieldAdmin,
                    $fieldName,
                    $options['type'],
                    $options['options']
                );
                continue;
            }
            $fields[$key] = $this->newEmbeddedFieldDescriptionInstance(
                $fieldAdmin,
                $fieldName,
                $options
            );
        }

        $field->setOption('fields', $fields);
    }

    private function newFieldDescriptionInstance(
        AdminInterface $admin,
        string $name,
        $type,
        array $options = []
    ): FieldDescriptionInterface {
        $fieldDescription = $this->newMinimalFieldDescription($admin, $name, $options);

        /** @var AdminInterface|AbstractAdmin $admin */
        $admin = $fieldDescription->getAdmin();
        if (!$fieldDescription->getLabel() && false !== $fieldDescription->getOption('label')) {
            $fieldDescription->setOption(
                'label',
                $admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label')
            );
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        if (!$type) {
            $guessType = $this->guesser->guessType(
                $admin->getClass(),
                $fieldDescription->getName(),
                $admin->getModelManager()
            );
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $builder = $admin->getShowBuilder();
        $builder->fixFieldDescription($admin, $fieldDescription);

        if ('grid' === $type) {
            $this->configureGrid($fieldDescription, $admin);
        }

        return $fieldDescription;
    }

    private function newEmbeddedFieldDescriptionInstance(
        AdminInterface $admin,
        string $name,
        array $options = []
    ): FieldDescriptionInterface {
        $description = $admin->getFieldDescriptionFactory()->create($admin->getClass(), $name);

        foreach ($description->getParentAssociationMappings() as $mapping) {
            $admin = $admin->getConfigurationPool()->getAdminByClass($mapping['targetEntity']);
            $name = explode('.', $name)[1];
            break;
        }

        $options = array_merge(
            ['fieldValueOnly' => true],
            $options
        );

        return $this->newFieldDescriptionInstance($admin, $name, null, $options);
    }

    private function newMinimalFieldDescription(
        AdminInterface $admin,
        string $name,
        array $options = []
    ): FieldDescriptionInterface {
        $fieldFactory = $admin->getFieldDescriptionFactory();
        $description = $fieldFactory->create($admin->getClass(), $name);

        foreach ($description->getParentAssociationMappings() as $mapping) {
            $admin = $admin->getConfigurationPool()->getAdminByClass($mapping['targetEntity']);
            $name = explode('.', $name)[1];
            break;
        }

        $fieldDescription = $fieldFactory->create(
            $admin->getClass(),
            $name,
            $options
        );

        $fieldDescription->setAdmin($admin);

        return $fieldDescription;
    }
}