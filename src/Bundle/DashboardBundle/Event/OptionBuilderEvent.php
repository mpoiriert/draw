<?php namespace Draw\Bundle\DashboardBundle\Event;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class OptionBuilderEvent extends Event
{
    private $action;

    private $openApiSchema;

    private $operation;

    private $options;

    private $request;

    private $response;

    public function __construct(
        Action $action,
        Operation $operation,
        Root $openApiSchema,
        Request $request,
        Response $response
    ) {
        $this->action = $action;
        $this->operation = $operation;
        $this->openApiSchema = $openApiSchema;
        $this->request = $request;
        $this->response = $response;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getOpenApiSchema(): Root
    {
        return $this->openApiSchema;
    }

    public function getOperation(): Operation
    {
        return $this->operation;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}