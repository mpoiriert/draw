<?php namespace Draw\Bundle\DashboardBundle\Action;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\OpenApiBundle\Controller\OpenApiController;
use Draw\Component\OpenApi\Schema\Operation;

class ActionFinder
{
    private $openApiController;

    public function __construct(OpenApiController $openApiController)
    {
        $this->openApiController = $openApiController;
    }

    public function findOneByOperationId($operationId): ?Action
    {
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

    public function findOneByPath($method, $path): ?Action
    {
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

    public function findOneByRoute($route): ?Action
    {
        $openApiSchema = $this->openApiController->loadOpenApiSchema();

        foreach ($openApiSchema->paths as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                $operationRoute = $operation->vendor['x-draw-open-api-symfony-route'] ?? null;
                if ($operationRoute === $route) {
                    return $this->getAction(
                        $operation,
                        $method,
                        $path
                    );
                }
            }
        }

        return null;
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
        return $action;
    }

    /**
     * @param $object
     * @return iterable|Action[]
     */
    public function findAllByByTarget($object): iterable
    {
        $rootSchema = $this->openApiController->loadOpenApiSchema();

        foreach ($rootSchema->paths as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                $action = $this->getAction($operation, $method, $path);
                if (!$action instanceof Action) {
                    continue;
                }

                foreach ($action->getTargets() as $target) {
                    if (!$object instanceof $target) {
                        continue;
                    }

                    yield clone $action;
                    break;
                }
            }
        }
    }
}