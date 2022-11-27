<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TagIfExpressionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $tagsConfiguration = $definition->getTags();
            foreach ($tagsConfiguration as $tagName => $tags) {
                foreach ($tags as $index => $tag) {
                    if (null !== ($tag['ifExpression'] ?? null)) {
                        $result = ReflectionAccessor::callMethod(
                            $container,
                            'getExpressionLanguage'
                        )->evaluate($tag['ifExpression'], ['container' => $this]);

                        if (!$result) {
                            unset($tagsConfiguration[$tagName][$index]);
                        }
                    }
                }
            }

            $definition->setTags($tagsConfiguration);
        }
    }
}
