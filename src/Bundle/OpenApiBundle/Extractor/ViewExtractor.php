<?php namespace Draw\Bundle\OpenApiBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Bundle\OpenApiBundle\View\View;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use ReflectionMethod;

class ViewExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $target
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$target instanceof Schema) {
            return false;
        }

        if (!$extractionContext->hasParameter('controller-reflection-method')) {
            return false;
        }

        if (!$this->getView($extractionContext->getParameter('controller-reflection-method'))) {
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
     * @param \ReflectionMethod $source
     * @param Schema $target
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $groups = [];

        if ($view = $this->getView($extractionContext->getParameter('controller-reflection-method'))) {
            $groups = $view->getSerializerGroups();
            if($statusCode = $view->getStatusCode()) {
                $extractionContext->setParameter('response-status-code', $statusCode);
            }

            /** @var Response $response */
            if($response = $extractionContext->getParameter('response')) {
                foreach($view->getHeaders() as $name => $header) {
                    $response->headers[$name] = $header;
                }
            }
        }

        if (empty($groups)) {
            $groups = [GroupsExclusionStrategy::DEFAULT_GROUP];
        }

        $modelContext = $extractionContext->getParameter('model-context', []);
        $modelContext['serializer-groups'] = $groups;
        $extractionContext->setParameter('model-context', $modelContext);
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return View|null
     */
    private function getView(ReflectionMethod $reflectionMethod)
    {
        /** @var View|null $view */
        $view = $this->annotationReader->getMethodAnnotation($reflectionMethod, View::class);
        return $view;
    }
}