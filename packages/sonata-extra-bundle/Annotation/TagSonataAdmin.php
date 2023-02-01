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

    public ?string $label;

    public ?string $manager_type;

    public ?string $group;

    public ?bool $show_in_dashboard;

    public ?bool $show_mosaic_button;

    public ?bool $keep_open;

    public ?bool $on_top;

    public ?string $icon;

    public ?string $label_translator_strategy;

    public ?string $label_catalogue;

    public ?string $pager_type;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
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
