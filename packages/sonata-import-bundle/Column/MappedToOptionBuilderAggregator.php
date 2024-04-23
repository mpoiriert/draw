<?php

namespace Draw\Bundle\SonataImportBundle\Column;

use Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilder\MappedToOptionBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class MappedToOptionBuilderAggregator
{
    public function __construct(
        /**
         * @var iterable<MappedToOptionBuilderInterface>
         */
        #[TaggedIterator(MappedToOptionBuilderInterface::class)]
        private iterable $mappedToOptionBuilders
    ) {
    }

    public function getOptions(Column $column): array
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
