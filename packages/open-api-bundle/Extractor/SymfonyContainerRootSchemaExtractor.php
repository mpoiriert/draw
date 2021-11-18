<?php

namespace Draw\Bundle\OpenApiBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Bundle\OpenApiBundle\Versioning\VersionMatcherInterface;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Tag;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class SymfonyContainerRootSchemaExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var VersionMatcherInterface
     */
    private $versionMatcher;

    public function __construct(Reader $reader, ?VersionMatcherInterface $versionMatcher = null)
    {
        $this->annotationReader = $reader;
        $this->versionMatcher = $versionMatcher;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof ContainerInterface) {
            return false;
        }

        if (!$target instanceof Root) {
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
     * @param ContainerInterface $source
     * @param Root               $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $this->triggerRouteExtraction($source->get('router'), $target, $extractionContext);
    }

    private function triggerRouteExtraction(
        RouterInterface $router,
        Root $schema,
        ExtractionContextInterface $extractionContext
    ): void {
        $versioning = $extractionContext->getParameter(PropertiesExtractor::CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY);
        foreach ($router->getRouteCollection() as $routeName => $route) {
            /* @var Route $route */
            if (!($path = $route->getPath())) {
                continue;
            }

            if ($versioning
                && $this->versionMatcher
                && !$this->versionMatcher->matchVersion($schema->info->version, $route)) {
                continue;
            }

            $controller = explode('::', $route->getDefault('_controller'));

            if (2 != count($controller)) {
                continue;
            }

            list($class, $method) = $controller;

            try {
                $reflectionMethod = new ReflectionMethod($class, $method);
            } catch (ReflectionException $exception) {
                continue;
            }

            $operation = $this->getOperation($route, $reflectionMethod);

            if (null === $operation) {
                continue;
            }

            if (!$operation->operationId) {
                $operation->operationId = $routeName;
            }
            $subContext = $extractionContext->createSubContext();
            $subContext->setParameter('symfony-route-name', $routeName);

            $extractionContext->getOpenApi()->extract($route, $operation, $subContext);
            $extractionContext->getOpenApi()->extract($reflectionMethod, $operation, $subContext);

            if (!isset($schema->paths[$path])) {
                $schema->paths[$path] = new PathItem();
            }

            $pathItem = $schema->paths[$path];

            foreach ($route->getMethods() as $method) {
                $pathItem->{strtolower($method)} = $operation;
            }
        }
    }

    /**
     * Return the operation for the route if the route is a Api route.
     */
    private function getOperation(Route $route, ReflectionMethod $method): ?Operation
    {
        $operation = $this->annotationReader->getMethodAnnotation($method, Operation::class);

        if ($operation instanceof Operation) {
            return $operation;
        }

        if ($route->getDefault('_draw_open_api')) {
            return new Operation();
        }

        foreach ($this->annotationReader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof Tag) {
                return new Operation();
            }
        }

        return null;
    }
}
