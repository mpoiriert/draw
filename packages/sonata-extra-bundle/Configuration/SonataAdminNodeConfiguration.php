<?php

namespace Draw\Bundle\SonataExtraBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\DependencyInjection\Definition;

class SonataAdminNodeConfiguration extends ArrayNodeDefinition
{
    public function __construct(
        string $entityClass,
        string $group,
        string $name = 'sonata',
        ?NodeParentInterface $parent = null,
    ) {
        parent::__construct($name, $parent);

        $this
            ->children()
                ->scalarNode('entity_class')->defaultValue($entityClass)->end()
                ->scalarNode('group')->defaultValue($group)->end()
                ->scalarNode('controller_class')->defaultValue('sonata.admin.controller.crud')->end()
                ->scalarNode('icon')->defaultNull()->end()
                ->scalarNode('label')->defaultNull()->end()
                ->scalarNode('translation_domain')->defaultValue('SonataAdminBundle')->end()
                ->scalarNode('show_in_dashboard')->defaultTrue()->end()
                ->enumNode('pager_type')->values(['default', 'simple'])->defaultValue('default')->end()
            ->end()
        ;
    }

    public function labelDefaultValue(?string $value): self
    {
        $this->children['label']->defaultValue($value);

        return $this;
    }

    public function controllerClassDefaultValue(?string $value): self
    {
        $this->children['controller_class']->defaultValue($value);

        return $this;
    }

    public function iconDefaultValue(?string $value): self
    {
        $this->children['icon']->defaultValue($value);

        return $this;
    }

    public function pagerTypeDefaultValue(?string $value): self
    {
        $this->children['pager_type']->defaultValue($value);

        return $this;
    }

    public function translationDomainDefaultValue(?string $value): self
    {
        $this->children['translation_domain']->defaultValue($value);

        return $this;
    }

    public static function configureFromConfiguration(Definition $definition, array $config): Definition
    {
        return $definition
            ->addTag(
                'sonata.admin',
                array_filter(
                    array_intersect_key(
                        $config,
                        array_flip(['group', 'icon', 'label', 'pager_type', 'show_in_dashboard', 'translation_domain'])
                    ) + ['manager_type' => 'orm', 'model_class' => $config['entity_class'], 'controller' => $config['controller_class']],
                    static fn ($value) => null !== $value
                )
            )
        ;
    }
}
