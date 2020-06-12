<?php

namespace Draw\Bundle\OpenApiBundle\Extractor;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathParameter;
use Symfony\Component\Routing\Route;

class RouteOperationExtractor implements ExtractorInterface
{
    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     *
     * @return bool
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof Route) {
            return false;
        }

        if (!$type instanceof Operation) {
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
     * @param Route     $source
     * @param Operation $type
     */
    public function extract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $type, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $type->setVendorDataKey('x-draw-open-api-symfony-route', $extractionContext->getParameter('symfony-route-name'));

        foreach ($source->compile()->getPathVariables() as $pathVariable) {
            foreach ($type->parameters as $parameter) {
                if ($parameter->name == $pathVariable) {
                    continue 2;
                }
            }

            $type->parameters[] = $pathParameter = new PathParameter();
            $pathParameter->name = $pathVariable;
            $pathParameter->type = 'string';
        }
    }
}
