<?php

declare(strict_types=1);

namespace Draw\Component\DataSynchronizer\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator('draw.data_synchronizer.serializer.construction.default', priority: 100)]
class MissingReferenceRegistryConstructor implements ObjectConstructorInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ObjectConstructorInterface $fallbackConstructor,
        private MissingReferenceRegistry $missingReferenceRegistry,
    ) {
    }

    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context,
    ): ?object {
        if ($result = $this->missingReferenceRegistry->find($metadata, $data)) {
            return $result;
        }

        $result = $this->fallbackConstructor->construct(
            $visitor,
            $metadata,
            $data,
            $type,
            $context
        );

        $this->missingReferenceRegistry->register($metadata, $data, $result);

        return $result;
    }
}
