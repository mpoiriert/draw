<?php

namespace Draw\Bundle\SonataExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTagInterface;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
class TagSonataAdmin implements ServiceTagInterface
{
    public ?string $model_class = null;

    public ?string $code = null;

    public ?string $controller = null;

    public ?string $label = null;

    public ?string $manager_type = null;

    public ?string $group = null;

    public ?bool $show_in_dashboard = null;

    public ?bool $show_mosaic_button = null;

    public ?bool $keep_open = null;

    public ?bool $on_top = null;

    public ?string $icon = null;

    public ?string $label_translator_strategy = null;

    public ?string $label_catalogue = null;

    public ?string $pager_type = null;

    public function __construct(private array $data)
    {
    }

    public function getName(): string
    {
        return 'sonata.admin';
    }

    public function getAttributes(): array
    {
        return array_filter(
            $this->data,
            fn ($value) => null !== $value
        );
    }
}
