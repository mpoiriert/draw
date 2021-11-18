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
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof Route) {
            return false;
        }

        if (!$target instanceof Operation) {
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
     * @param Operation $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $target->setVendorDataKey('x-draw-open-api-symfony-route', $extractionContext->getParameter('symfony-route-name'));

        foreach ($source->compile()->getPathVariables() as $pathVariable) {
            foreach ($target->parameters as $parameter) {
                if ($parameter->name == $pathVariable) {
                    continue 2;
                }
            }

            $target->parameters[] = $pathParameter = new PathParameter();
            $pathParameter->name = $pathVariable;
            $pathParameter->type = 'string';
        }
    }
}
