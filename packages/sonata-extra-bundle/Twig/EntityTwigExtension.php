<?php

namespace Draw\Bundle\SonataExtraBundle\Twig;

use Doctrine\Common\Util\ClassUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EntityTwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('entity_to_string', [$this, 'entityToString']),
        ];
    }

    public function entityToString($entity): string
    {
        if (method_exists($entity, '__toString')) {
            return (string) $entity;
        }

        return sprintf('%s:%s', ClassUtils::getClass($entity), spl_object_hash($entity));
    }
}
