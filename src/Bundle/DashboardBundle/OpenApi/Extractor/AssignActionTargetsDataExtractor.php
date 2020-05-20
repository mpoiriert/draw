<?php namespace Draw\Bundle\DashboardBundle\OpenApi\Extractor;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Annotations\Targets;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use ReflectionMethod;

class AssignActionTargetsDataExtractor implements ExtractorInterface
{
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if(!$source instanceof ReflectionMethod) {
            return false;
        }

        if(!$target instanceof Operation) {
            return false;
        }

        return true;
    }

    /**
     * @param ReflectionMethod $source
     * @param Operation $target
     * @param ExtractionContextInterface $extractionContext
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $targets = $target->getVendorData()['x-draw-dashboard-targets'] ?? null;
        if(!$targets instanceof Targets) {
            return;
        }

        $action = $target->getVendorData()['x-draw-dashboard-action'] ?? null;

        if(!$action instanceof Action) {
            return;
        }

        if($action->getTargets() !== null) {
            return;
        }

        $action->setTargets($targets->getTargets());
    }
}