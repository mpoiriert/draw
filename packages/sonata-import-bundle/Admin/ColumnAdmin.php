<?php

namespace Draw\Bundle\SonataImportBundle\Admin;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @method Column getSubject()
 */
#[AutoconfigureTag(
    'sonata.admin',
    attributes: [
        'group' => 'Import',
        'show_in_dashboard' => false,
        'model_class' => Column::class,
        'manager_type' => 'orm',
        'label' => 'Column',
    ]
)]
class ColumnAdmin extends AbstractAdmin
{
    public function __construct(
        private Importer $importer
    ) {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add(
                'headerName',
                options: [
                    'disabled' => true,
                ]
            )
            ->add(
                'sample',
                options: [
                    'required' => false,
                    'disabled' => true,
                ]
            )
            ->add(
                'mappedTo',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $this->loadMappedToOptions($this->getSubject()),
                    'attr' => [
                        'data-sonata-select2' => 'false',
                    ],
                ]
            )
            ->add('isIdentifier')
            ->add('isDate')
            ->add('isIgnored');
    }

    private function loadMappedToOptions(Column $column): array
    {
        $options = $this->importer->getOptions($column);

        $result = [];
        // Iterate over each element in the original array
        foreach ($options as $option) {
            $parts = explode('.', $option);
            if (1 === \count($parts)) {
                $result[$option] = $option;
                continue;
            }

            $result[$parts[0]][$option] = $option;
        }

        return $result;
    }
}
