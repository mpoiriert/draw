<?php

namespace Draw\Bundle\SonataExtraBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

trait BlockTrait
{
    public function getName(): string
    {
        return '';
    }

    public function getJavascripts($media): array
    {
        return [];
    }

    public function getStylesheets($media): array
    {
        return [];
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        throw new \RuntimeException('This method should not be use. Implement configureSetting('.OptionsResolver::class.') instead.');
    }

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
