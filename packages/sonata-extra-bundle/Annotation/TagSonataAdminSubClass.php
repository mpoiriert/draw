<?php

namespace Draw\Bundle\SonataExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTagInterface;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
class TagSonataAdminSubClass implements ServiceTagInterface
{
    public ?string $sub_class = null;

    public ?string $label = null;

    public function __construct(private array $data)
    {
    }

    public function getName(): string
    {
        return 'sonata.admin.sub_class';
    }

    public function getAttributes(): array
    {
        return array_filter(
            $this->data,
            fn ($value) => null !== $value
        );
    }
}
