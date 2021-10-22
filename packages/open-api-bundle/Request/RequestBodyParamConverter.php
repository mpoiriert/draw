<?php

namespace Draw\Bundle\OpenApiBundle\Request;

use Draw\Bundle\OpenApiBundle\Util\DynamicArrayObject;
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

class RequestBodyParamConverter implements ParamConverterInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $object = $this->deserialize(
            $this->getBodyData($request, $configuration),
            $configuration
        );

        $request->attributes->set($configuration->getName(), $object);

        if (!$request->attributes->get('_draw_dummy_execution')) {
            $request->attributes->set('_draw_body_validation', $configuration);
        }

        return true;
    }

    private function getBodyData(Request $request, ParamConverter $configuration)
    {
        switch (true) {
            case $request->attributes->get('_draw_dummy_execution'):
                $requestData = [];
                break;
            case 0 === strpos($request->headers->get('Content-Type'), 'application/json'):
                //This allow a empty body to be consider as '{}'
                if (null === ($requestData = json_decode($request->getContent(), true))) {
                    $requestData = [];
                }
                break;
            case 0 === strpos($request->headers->get('Content-Type'), 'multipart/form-data'):
                $requestData = $request->request->all();
                break;
            default:
                throw new UnsupportedMediaTypeHttpException();
        }

        return json_encode($this->assignPropertiesFromAttribute($request, $configuration, $requestData));
    }

    private function assignPropertiesFromAttribute(Request $request, ParamConverter $configuration, $requestData)
    {
        $options = (array) $configuration->getOptions();
        if (!isset($options['propertiesMap'])) {
            return $requestData;
        }

        $content = new DynamicArrayObject($requestData);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $attributes = (object) $request->attributes->all();
        foreach ($options['propertiesMap'] as $target => $source) {
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

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return null !== $configuration->getClass() && 'draw_open_api.request_body' === $configuration->getConverter();
    }

    protected function configureContext(DeserializationContext $context, array $options)
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
