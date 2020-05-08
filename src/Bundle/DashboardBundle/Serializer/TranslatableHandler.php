<?php namespace Draw\Bundle\DashboardBundle\Serializer;

use Draw\Bundle\DashboardBundle\Annotations\Translatable;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatableHandler implements SubscribingHandlerInterface
{
    private $translator;

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Translatable::class,
                'method' => 'serializeTranslatableToJson',
            ]
        ];
    }

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function serializeTranslatableToJson(
        JsonSerializationVisitor $visitor,
        Translatable $translatable,
        array $type,
        Context $context
    ) {
        if ($translatable === null || empty($translatable->getToken())) {
            return null;
        }

        return $this->translator->trans(
            $translatable->getToken(),
            [],
            $translatable->getDomain() ?: 'DrawDashboardBundle'
        );
    }
}