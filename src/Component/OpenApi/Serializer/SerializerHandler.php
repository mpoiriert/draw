<?php

namespace Draw\Component\OpenApi\Serializer;

use Draw\Component\OpenApi\Schema\Mixed;
use Draw\Component\OpenApi\Schema\SecurityRequirement;
use Draw\Component\OpenApi\Schema\Vendor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class SerializerHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => SecurityRequirement::class,
                'method' => 'serializeSecurityRequirementToJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => SecurityRequirement::class,
                'method' => 'deserializeSecurityRequirementToJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Mixed::class,
                'method' => 'serializeMixedToJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => Mixed::class,
                'method' => 'deserializeMixedToJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Vendor::class,
                'method' => 'serializeVendorToJson',
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function serializeSecurityRequirementToJson(
        JsonSerializationVisitor $visitor,
        SecurityRequirement $securityRequirement,
        array $type,
        Context $context
    ) {
        return $securityRequirement->getData();
    }

    /**
     * @param SecurityRequirement $data
     *
     * @return SecurityRequirement
     */
    public function deserializeSecurityRequirementToJson(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        $securityRequirement = new SecurityRequirement();
        $securityRequirement->setData($data);

        return $securityRequirement;
    }

    /**
     * @return mixed
     */
    public function serializeMixedToJson(
        JsonSerializationVisitor $visitor,
        Mixed $mixed,
        array $type,
        Context $context
    ) {
        return $mixed->data;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function deserializeMixedToJson(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        return $data;
    }

    /**
     * @return mixed
     */
    public function serializeVendorToJson(
        JsonSerializationVisitor $visitor,
        Vendor $vendor,
        array $type,
        Context $context
    ) {
        return $vendor->value;
    }
}
