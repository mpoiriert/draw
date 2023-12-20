<?php

namespace Draw\Component\OpenApi\Request\ValueResolver;

use Draw\Component\Core\DynamicArrayObject;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\Exception as JMSSerializerException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RequestBodyValueResolver implements ValueResolverInterface
{
    public static function getDefaultNamePriority(): int
    {
        return 115; // Need to be before EntityValueResolver which is 110
    }

    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attributes = $argument->getAttributes(RequestBody::class, ArgumentMetadata::IS_INSTANCEOF);

        if (empty($attributes)) {
            return [];
        }

        $attribute = $attributes[0];

        \assert($attribute instanceof RequestBody);

        $object = $this->deserialize(
            $attribute->type ?? $argument->getType(),
            $attribute,
            $this->getBodyData($request, $attribute),
        );

        $attribute->argumentName = $argument->getName();
        $request->attributes->set($argument->getName(), $object);
        $request->attributes->set('_draw_body_validation', $attribute);

        yield $object;
    }

    private function getBodyData(Request $request, RequestBody $attribute): string
    {
        $contentType = $request->headers->get('Content-Type');
        switch (true) {
            case str_starts_with($contentType, 'application/json'):
                // This allows an empty body to be consider as '{}'
                try {
                    $requestData = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $requestData = [];
                }

                break;
            case str_starts_with($contentType, 'multipart/form-data'):
                $requestData = $request->request->all();
                break;
            default:
                throw new UnsupportedMediaTypeHttpException('Unsupported request Content-Type ['.$contentType.']');
        }

        $result = json_encode(
            $this->assignPropertiesFromAttribute($request, $attribute->propertiesMap ?? [], $requestData),
            \JSON_THROW_ON_ERROR
        );

        return '[]' === $result ? '{}' : $result;
    }

    private function assignPropertiesFromAttribute(Request $request, array $propertiesMap, array $requestData): array
    {
        if (!$propertiesMap) {
            return $requestData;
        }

        $content = new DynamicArrayObject($requestData);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $attributes = (object) $request->attributes->all();
        foreach ($propertiesMap as $target => $source) {
            $propertyAccessor->setValue(
                $content,
                $target,
                $propertyAccessor->getValue($attributes, $source)
            );
        }

        return $content->getArrayCopy();
    }

    private function deserialize(string $type, RequestBody $attribute, string $data)
    {
        try {
            return $this->serializer->deserialize(
                $data,
                $type,
                'json',
                $this->createContext($attribute)
            );
        } catch (UnsupportedFormatException $e) {
            throw new UnsupportedMediaTypeHttpException($e->getMessage(), $e);
        } catch (JMSSerializerException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    private function createContext(RequestBody $attribute): DeserializationContext
    {
        $context = new DeserializationContext();

        if ($attribute->deserializationGroups) {
            $context->setGroups($attribute->deserializationGroups);
        }

        foreach ($attribute->deserializationContext as $key => $value) {
            switch ($key) {
                case 'groups':
                    if ($value) {
                        $context->setGroups($value);
                    }
                    break;
                case 'version':
                    $context->setVersion($value);
                    break;
                case 'maxDepth':
                case 'enableMaxDepth':
                    if ($value) {
                        $context->enableMaxDepthChecks();
                    }
                    break;
                default:
                    $context->setAttribute($key, $value);
                    break;
            }
        }

        return $context;
    }
}
