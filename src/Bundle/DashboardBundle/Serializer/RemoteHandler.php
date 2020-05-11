<?php namespace Draw\Bundle\DashboardBundle\Serializer;

use Draw\Bundle\DashboardBundle\Annotations\Remote;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RemoteHandler implements SubscribingHandlerInterface
{
    private $urlGenerator;

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Remote::class,
                'method' => 'serializeRemoteToJson',
            ]
        ];
    }

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function serializeRemoteToJson(
        JsonSerializationVisitor $visitor,
        Remote $remote,
        array $type,
        Context $context
    ) {
        $result = [
            'url' => $this->urlGenerator->generate($remote->getRouteName(), [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        if($remote->getFormPathValue()) {
            $result['fromPathValue'] = $remote->getFormPathValue();
        }

        return $result;
    }
}