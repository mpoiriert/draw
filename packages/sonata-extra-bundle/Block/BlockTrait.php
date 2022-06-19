<?php

namespace Draw\Bundle\SonataExtraBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait BlockTrait
{
    abstract public function configureSettings(OptionsResolver $resolver);

    public function getCacheKeys(BlockInterface $block): array
    {
        return [
            'block_id' => $block->getId(),
            'updated_at' => $block->getUpdatedAt() ? $block->getUpdatedAt()->format('U') : strtotime('now'),
        ];
    }

    public function load(BlockInterface $block): void
    {
    }
}
