<?php namespace Draw\Bundle\OpenApiBundle\Request;

use Draw\DrawBundle\PropertyAccess\DynamicArrayObject;
use Draw\DrawBundle\Request\Exception\RequestValidationException;
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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBodyParamConverter implements ParamConverterInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var DeserializeBody
     */
    private $defaultConfiguration;

    /**
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param DeserializeBody $defaultConfiguration
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        DeserializeBody $defaultConfiguration
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->defaultConfiguration = $defaultConfiguration;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array)$configuration->getOptions();

        switch(true) {
            case strpos($request->headers->get('Content-Type'), 'application/json') === 0:
                //This allow a empty body to be consider as '{}'
                if (is_null($requestData = json_decode($request->getContent(), true))) {
                    $requestData = [];
                }
                break;
            case strpos($request->headers->get('Content-Type'), 'multipart/form-data') === 0:
                $requestData = $request->request->all();
                break;
            default:
                throw new \RuntimeException('Invalid request format');
        }

        if (isset($options['propertiesMap'])) {
            $content = new DynamicArrayObject($requestData);
            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            $attributes = (object)$request->attributes->all();
            foreach ($options['propertiesMap'] as $target => $source) {
                $propertyAccessor->setValue(
                    $content,
                    $target,
                    $propertyAccessor->getValue($attributes, $source)
                );
            }

            $requestData = $content->getArrayCopy();
        }

        $object = $this->deserialize(
            json_encode($requestData),
            $configuration
        );

        $request->attributes->set($configuration->getName(), $object);

        $violations = $this->validate($object, $configuration);

        if(count($violations)) {
            $exception = new RequestValidationException();
            $exception->setViolationList($violations);
            throw $exception;
        }

        return true;
    }

    private function deserialize($data, ParamConverter $configuration)
    {
        $defaultOptions = $this->defaultConfiguration->getOptions();
        $options = $configuration->getOptions();

        $arrayContext = array_merge(
            $defaultOptions['deserializationContext'] ?? [],
            $options['deserializationContext'] ?? []
        );

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
     * @param $object
     * @param ParamConverter $paramConverter
     * @return ConstraintViolationListInterface|null
     */
    private function validate($object, ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if($options['validate'] ?? true) {
            $validatorOptions = $paramConverter->getOptions()['validator'] ?? [];
            $groups = $validatorOptions['groups'] ?? ['Default'];
            return $this->validator->validate($object, null, $groups);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return null !== $configuration->getClass() && 'draw_open_api.request_body' === $configuration->getConverter();
    }

    /**
     * @param DeserializationContext $context
     * @param array   $options
     */
    protected function configureContext(DeserializationContext $context, array $options)
    {
        foreach ($options as $key => $value) {
            switch($key) {
                case 'groups':
                    if($value) {
                        $context->setGroups($value);
                    }
                    break;
                case 'version':
                    $context->setVersion($value);
                    break;
                case 'maxDepth':
                case 'enableMaxDepth':
                    if($value) {
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