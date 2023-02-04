<?php

namespace Draw\Component\OpenApi\Naming;

class AliasesClassNamingFilter implements ClassNamingFilterInterface
{
    /**
     * @param array<mixed, array<string, string>> $definitionAliases
     */
    public function __construct(private array $definitionAliases)
    {
    }

    public function filterClassName(string $originalClassName, array $context = [], ?string $newName = null): string
    {
        $className = $newName ?: $originalClassName;
        foreach ($this->definitionAliases as $configuration) {
            $class = $configuration['class'];
            $alias = $configuration['alias'];
            if (str_ends_with($class, '\\')) {
                if (str_starts_with($className, $class)) {
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
