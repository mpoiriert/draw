<?php

namespace Draw\Bundle\SonataImportBundle\Admin;

use Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilder\MappedToOptionBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
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
        #[TaggedIterator(MappedToOptionBuilderInterface::class)]
        private iterable $mappedToOptionBuilders
    ) {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('headerName')
            ->add('sample')
            ->add(
                'mappedTo',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $this->loadMappedToOptions($this->getSubject()),
                ]
            )
            ->add('isIdentifier')
            ->add('isDate')
            ->add('isIgnored');
    }

    private function loadMappedToOptions(Column $column): array
    {
        $options = [];

        foreach ($this->mappedToOptionBuilders as $mappedToOptionBuilder) {
            $options = $mappedToOptionBuilder->getOptions(
                $column,
                $options
            );
        }

        return $options;
    }
}
