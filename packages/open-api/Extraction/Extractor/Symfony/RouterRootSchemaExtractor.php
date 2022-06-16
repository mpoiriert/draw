<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Symfony;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Tag;
use Draw\Component\OpenApi\Versioning\RouteVersionMatcherInterface;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RouterRootSchemaExtractor implements ExtractorInterface
{
    private Reader $annotationReader;

    private ?RouteVersionMatcherInterface $versionMatcher;

    public static function getDefaultPriority(): int
    {
        return 256;
    }

    public function __construct(Reader $reader, ?RouteVersionMatcherInterface $versionMatcher = null)
    {
        $this->annotationReader = $reader;
        $this->versionMatcher = $versionMatcher;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof RouterInterface) {
            return false;
        }

        if (!$target instanceof Root) {
            return false;
        }

        return true;
    }

    /**
     * @param RouterInterface $source
     * @param Root            $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $versioning = $extractionContext->getParameter(PropertiesExtractor::CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY);
        foreach ($source->getRouteCollection() as $routeName => $route) {
            /* @var Route $route */
            if (!($path = $route->getPath())) {
                continue;
            }

            if ($versioning
                && $this->versionMatcher
                && !$this->versionMatcher->matchVersion($target->info->version, $route)) {
                continue;
            }

            $controller = explode('::', $route->getDefault('_controller'));

            if (2 != \count($controller)) {
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

            if (!isset($target->paths[$path])) {
                $target->paths[$path] = new PathItem();
            }

            $pathItem = $target->paths[$path];

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
