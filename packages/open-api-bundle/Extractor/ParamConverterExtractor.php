<?php

namespace Draw\Bundle\OpenApiBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Schema;
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
     * @param $target
     *
     * @return bool
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$target instanceof Operation) {
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
     * @param ReflectionMethod $source
     * @param Operation        $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $paramConverter = $this->getParamConverter($source);
        if (null === ($type = $paramConverter->getClass())) {
            foreach ($source->getParameters() as $parameter) {
                if ($parameter->getName() != $paramConverter->getName()) {
                    continue;
                }
                $type = $parameter->getClass()->getName();
            }
        }

        $target->parameters[] = $parameter = new BodyParameter();

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

        return null;
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

                if ('draw_open_api.request_body' != $converter->getConverter()) {
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
