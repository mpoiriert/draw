<?php

namespace Draw\Component\OpenApi\Request\ParamConverter;

use Draw\Component\Core\DynamicArrayObject;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\Exception as JMSSerializerException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DeserializeBodyParamConverter implements ParamConverterInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return null !== $configuration->getClass() && 'draw_open_api.request_body' === $configuration->getConverter();
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $object = $this->deserialize(
            $this->getBodyData($request, $configuration),
            $configuration
        );

        $request->attributes->set($configuration->getName(), $object);
        $request->attributes->set('_draw_body_validation', $configuration);

        return true;
    }

    private function getBodyData(Request $request, ParamConverter $configuration): string
    {
        $contentType = $request->headers->get('Content-Type');
        switch (true) {
            case 0 === strpos($contentType, 'application/json'):
                // This allow a empty body to be consider as '{}'
                if (null === ($requestData = json_decode($request->getContent(), true))) {
                    $requestData = [];
                }
                break;
            case 0 === strpos($contentType, 'multipart/form-data'):
                $requestData = $request->request->all();
                break;
            default:
                throw new UnsupportedMediaTypeHttpException('Unsupported request Content-Type ['.$contentType.']');
        }

        $result = json_encode($this->assignPropertiesFromAttribute($request, $configuration, $requestData));

        return '[]' === $result ? '{}' : $result;
    }

    private function assignPropertiesFromAttribute(Request $request, ParamConverter $configuration, $requestData): array
    {
        $propertiesMap = $configuration->getOptions()['propertiesMap'] ?? null;
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

    private function deserialize($data, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        $arrayContext = $options['deserializationContext'] ?? [];

        $this->configureContext($context = new DeserializationContext(), $arrayContext);

        try {
            return $this->serializer->deserialize(
                $data,
                $configuration->getClass(),
                'json',
                $context
            );
        } catch (UnsupportedFormatException $e) {
            throw new UnsupportedMediaTypeHttpException($e->getMessage(), $e);
        } catch (JMSSerializerException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    protected function configureContext(DeserializationContext $context, array $options): void
    {
        foreach ($options as $key => $value) {
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
    }
}
