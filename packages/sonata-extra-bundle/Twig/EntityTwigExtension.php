<?php

namespace Draw\Bundle\SonataExtraBundle\Twig;

use Doctrine\Common\Util\ClassUtils;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFilter;

class EntityTwigExtension
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    #[AsTwigFilter(name: 'entity_to_string')]
    public function entityToString($entity): string
    {
        if (method_exists($entity, '__toString')) {
            return (string) $entity;
        }

        return \sprintf('%s:%s', ClassUtils::getClass($entity), spl_object_hash($entity));
    }

    #[AsTwigFilter(name: 'translate_label')]
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
