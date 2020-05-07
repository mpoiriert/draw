<?php namespace Draw\Bundle\DashboardBundle\Action;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\OpenApiBundle\Controller\OpenApiController;
use Draw\Component\OpenApi\Schema\Operation;

class ActionFinder
{
    private $openApiController;

    private $actionsByOperationId = [];

    private $actionsByPath = [];

    private $actionsByRoute = [];

    private $actionsByClass = [];

    private $classesHierarchy = [];

    public function __construct(OpenApiController $openApiController)
    {
        $this->openApiController = $openApiController;
    }

    public function findOneByOperationId($operationId): ?Action
    {
        if (!array_key_exists($operationId, $this->actionsByOperationId)) {
            // Doesn't exists until proof otherwise
            $this->actionsByOperationId[$operationId] = null;
            $schema = $this->openApiController->loadOpenApiSchema();
            foreach ($schema->paths as $path => $pathItem) {
                foreach ($pathItem->getOperations() as $method => $operation) {
                    if ($operation->operationId !== $operationId) {
                        continue;
                    }

                    return $this->getAction($operation, $method, $path);
                }
            }
        }

        return $this->cloneActionIfNotNull($this->actionsByOperationId[$operationId]);
    }

    public function findOneByPath($method, $path): ?Action
    {
        $key = $method . ' ' . $path;
        if (!array_key_exists($key, $this->actionsByPath)) {
            // Doesn't exists until proof otherwise
            $this->actionsByPath[$key] = null;
            $openApiSchema = $this->openApiController->loadOpenApiSchema();

            $method = strtolower($method);
            if (is_null($pathItem = $openApiSchema->paths[$path] ?? null)) {
                return null;
            }

            if (is_null($operation = $pathItem->getOperations()[$method] ?? null)) {
                return null;
            }

            return $this->getAction($operation, $method, $path);
        }

        return $this->cloneActionIfNotNull($this->actionsByPath[$key]);
    }

    public function findOneByRoute($route): ?Action
    {
        if (!array_key_exists($route, $this->actionsByRoute)) {
            // Doesn't exists until proof otherwise
            $this->actionsByPath[$route] = null;
            $openApiSchema = $this->openApiController->loadOpenApiSchema();

            foreach ($openApiSchema->paths as $path => $pathItem) {
                foreach ($pathItem->getOperations() as $method => $operation) {
                    $operationRoute = $operation->vendor['x-draw-open-api-symfony-route'] ?? null;
                    if ($operationRoute !== $route) {
                        continue;
                    }

                    return $this->getAction(
                        $operation,
                        $method,
                        $path
                    );
                }
            }
        }

        return $this->cloneActionIfNotNull($this->actionsByRoute[$route]);
    }

    private function getAction(Operation $operation, $method, $path): ?Action
    {
        /** @var Action $action */
        if (is_null($action = $operation->vendor['x-draw-dashboard-action'] ?? null)) {
            return null;
        }

        $routeName = $operation->getVendorData()['x-draw-open-api-symfony-route'] ?? null;

        $action = clone $action;
        $action->setPath($path);
        $action->setRouteName($routeName);
        $action->setMethod($method);
        $action->setOperation($operation);

        $this->actionsByRoute[$routeName] = $action;
        $this->actionsByPath[$method . ' ' . $path] = $action;
        $this->actionsByOperationId[$operation->operationId] = $action;

        return $action;
    }

    /**
     * @param $object
     * @return iterable|Action[]
     */
    public function findAllByByTarget($object): array
    {

        $class = get_class($object);
        if (!array_key_exists($class, $this->actionsByClass)) {
            // Doesn't exists until proof otherwise
            $this->actionsByClass[$class] = [];

            $classes = $this->getClassesHierarchy($object);
            $rootSchema = $this->openApiController->loadOpenApiSchema();

            foreach ($rootSchema->paths as $path => $pathItem) {
                foreach ($pathItem->getOperations() as $method => $operation) {
                    $action = $this->getAction($operation, $method, $path);
                    if (!$action instanceof Action) {
                        continue;
                    }

                    if (!array_intersect($action->getTargets(), $classes)) {
                        continue;
                    }

                    $this->actionsByClass[$class][] = $this->getAction($operation, $method, $path);
                }
            }

            return $this->actionsByClass[$class];
        }

        return array_map(
            function (Action $action) {
                return clone $action;
            },
            $this->actionsByClass[$class]
        );
    }

    private function cloneActionIfNotNull(?Action $action): ?Action
    {
        return $action ? clone $action : null;
    }

    private function getClassesHierarchy($object)
    {
        $class = get_class($object);
        if (!array_key_exists($class, $this->classesHierarchy)) {
            $classes = array_merge(
                [$class],
                class_implements($class),
                class_parents($class)
            );

            $this->classesHierarchy[$class] = array_values($classes);
        }

        return $this->classesHierarchy[$class];
    }
}