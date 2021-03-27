<?php namespace Draw\Component\OpenApi\Naming;

class ReferenceContextClassNamingFilter implements ClassNamingFilterInterface
{
    public function filterClassName(string $originalClassName, array $context = [], string $newName = null)
    {
        $groups = $context['serializer-groups'] ?? [];
        if (1 === count($groups) && 'reference' === strtolower($groups[0])) {
            $newName .= 'Reference';
        }

        return $newName;
    }

}