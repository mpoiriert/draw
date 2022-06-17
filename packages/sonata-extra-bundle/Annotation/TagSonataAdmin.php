<?php

namespace Draw\Bundle\SonataExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTagInterface;

/**
 * @Annotation
 * @Target("CLASS")
 */
class TagSonataAdmin implements ServiceTagInterface
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $manager_type;

    /**
     * @var string
     */
    public $group;

    /**
     * @var bool
     */
    public $show_in_dashboard;

    /**
     * @var bool
     */
    public $show_mosaic_button;

    /**
     * @var bool
     */
    public $keep_open;

    /**
     * @var bool
     */
    public $on_top;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var string
     */
    public $label_translator_strategy;

    /**
     * @var string
     */
    public $label_catalogue;

    /**
     * @var string
     */
    public $pager_type;

    private $data = [];

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
