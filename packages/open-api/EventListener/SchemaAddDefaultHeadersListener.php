<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Schema\HeaderParameter;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SchemaAddDefaultHeadersListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreDumpRootSchemaEvent::class => ['addHeaders', 255],
        ];
    }

    public function __construct(
        private array $headers,
        private ArrayTransformerInterface $arrayTransformer,
    ) {
    }

    public function addHeaders(PreDumpRootSchemaEvent $event): void
    {
        $root = $event->getSchema();

        if (!$root->paths) {
            return;
        }

        $headers = [];
        foreach ($this->headers as $data) {
            $headers[] = $this->arrayTransformer->fromArray($data, HeaderParameter::class);
        }

        foreach ($root->paths as $pathItem) {
            foreach ($pathItem->getOperations() as $operation) {
                $operation->parameters = array_merge($operation->parameters, $headers);
            }
        }
    }
}
