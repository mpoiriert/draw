<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Schema\HeaderParameter;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SchemaAddDefaultHeadersListener implements EventSubscriberInterface
{
    private ArrayTransformerInterface $arrayTransformer;

    private array $headers;

    public static function getSubscribedEvents(): array
    {
        return [
            PreDumpRootSchemaEvent::class => ['addHeaders', 255],
        ];
    }

    public function __construct(array $headers, ArrayTransformerInterface $arrayTransformer)
    {
        $this->headers = $headers;
        $this->arrayTransformer = $arrayTransformer;
    }

    public function addHeaders(PreDumpRootSchemaEvent $event): void
    {
        $root = $event->getSchema();

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
