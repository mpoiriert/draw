<?php

namespace Draw\Component\OpenApi\Naming;

class AliasesClassNamingFilter implements ClassNamingFilterInterface
{
    /**
     * @var string[]
     */
    private array $definitionAliases;

    public function __construct(array $definitionAliases)
    {
        $this->definitionAliases = $definitionAliases;
    }

    public function filterClassName(string $originalClassName, array $context = [], string $newName = null): string
    {
        $className = $newName ?: $originalClassName;
        foreach ($this->definitionAliases as $configuration) {
            $class = $configuration['class'];
            $alias = $configuration['alias'];
            if ('\\' === substr($class, -1)) {
                if (0 === strpos($className, $class)) {
                    return str_replace($class, $alias, $className);
                }
                continue;
            }

            if ($class == $className) {
                return $alias;
            }
        }

        return $className;
    }
}
