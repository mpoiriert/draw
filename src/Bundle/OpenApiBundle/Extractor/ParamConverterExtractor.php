<?php namespace Draw\Bundle\OpenApiBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Operation;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ParamConverterExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
            return false;
        }

        if (!$this->getParamConverter($source)) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionMethod $method
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($method, $operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $paramConverter = $this->getParamConverter($method);
        if (is_null($type = $paramConverter->getClass())) {
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->getName() != $paramConverter->getName()) {
                    continue;
                }
                $type = $parameter->getClass()->getName();
            }
        }

        $operation->parameters[] = $parameter = new BodyParameter();

        $serializationGroups = $this->getDeserializationGroups($paramConverter);
        $validationGroups = $this->getValidationGroups($paramConverter);

        $subContext = $extractionContext->createSubContext();
        $modelContext = $subContext->getParameter('model-context', []);

        if ($serializationGroups) {
            $modelContext['serializer-groups'] = $serializationGroups;
        }

        if ($validationGroups) {
            $modelContext['validation-groups'] = $validationGroups;
        }

        $subContext->setParameter('model-context', $modelContext);

        $subContext->getOpenApi()->extract(
            $type,
            $parameter->schema = new Schema(),
            $subContext
        );
    }

    private function getDeserializationGroups(ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if (isset($options['deserializationContext']['groups'])) {
            return $options['deserializationContext']['groups'];
        }

        return [GroupsExclusionStrategy::DEFAULT_GROUP];
    }

    private function getValidationGroups(ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if (isset($options['validator']['groups'])) {
            return $options['validator']['groups'];
        }

        return null;
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return ParamConverter|null
     */
    private function getParamConverter(ReflectionMethod $reflectionMethod)
    {
        $converters = array_filter(
            $this->reader->getMethodAnnotations($reflectionMethod),
            function ($converter) {
                if (!$converter instanceof ParamConverter) {
                    return false;
                }

                if ($converter->getConverter() != "draw_open_api.request_body") {
                    return false;
                }

                $options = $converter->getOptions();
                if (isset($options['draw_open_api']['disable']) && $options['draw_open_api']['disable']) {
                    return false;
                }

                return true;
            }
        );

        return reset($converters);
    }
}