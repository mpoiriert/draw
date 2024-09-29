<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Symfony;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathParameter;
use Symfony\Component\Routing\Route;

class RouteOperationExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 128;
    }

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
                if ($parameter->name === $pathVariable) {
                    continue 2;
                }
            }

            $target->parameters[] = new PathParameter($pathVariable);
        }
    }
}
