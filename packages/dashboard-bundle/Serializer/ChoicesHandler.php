<?php

namespace Draw\Bundle\DashboardBundle\Serializer;

use Draw\Bundle\DashboardBundle\Annotations\Choices;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

class ChoicesHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Choices::class,
                'method' => 'serializeChoicesToJson',
            ],
        ];
    }

    public function serializeChoicesToJson(
        JsonSerializationVisitor $visitor,
        Choices $choices,
        array $type,
        Context $context
    ) {
        return $context->getNavigator()->accept($choices->toArray());
    }
}
