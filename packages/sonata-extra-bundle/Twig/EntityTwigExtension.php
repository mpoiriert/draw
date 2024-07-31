<?php

namespace Draw\Bundle\SonataExtraBundle\Twig;

use Doctrine\Common\Util\ClassUtils;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EntityTwigExtension extends AbstractExtension
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('entity_to_string', [$this, 'entityToString']),
            new TwigFilter('translate_label', [$this, 'translateLabel']),
        ];
    }

    public function entityToString($entity): string
    {
        if (method_exists($entity, '__toString')) {
            return (string) $entity;
        }

        return sprintf('%s:%s', ClassUtils::getClass($entity), spl_object_hash($entity));
    }

    public function translateLabel(array|AdminAction $data): string
    {
        if ($data instanceof AdminAction) {
            $translationDomain = $data->getTranslationDomain();
            $value = $data->getLabel();
        } else {
            $value = $data['label'];
            $translationDomain = $data['translation_domain'] ?? $data['translationDomain'] ?? null;
        }

        if (null === $translationDomain || false === $translationDomain) {
            return $value;
        }

        return $this->translator->trans(
            $value,
            [],
            $translationDomain
        );
    }
}
