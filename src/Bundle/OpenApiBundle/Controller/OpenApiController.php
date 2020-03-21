<?php namespace Draw\Bundle\OpenApiBundle\Controller;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiController
{
    private $openApi;
    private $parameterBag;
    private $container;
    private $openApiSchema;

    public function __construct(
        OpenApi $openApi,
        ParameterBagInterface $parameterBag,
        ContainerInterface $container
    )
    {
        $this->openApi = $openApi;
        $this->parameterBag = $parameterBag;
        $this->container = $container;
    }

    public function apiDocAction(
        Request $request,
        UrlGeneratorInterface $urlGenerator
    ) {
        if ($request->getRequestFormat() != 'json') {
            $currentRoute = $request->attributes->get('_route');
            $currentUrl = $urlGenerator->generate($currentRoute, array('_format' => 'json'), true);
            return new RedirectResponse('http://petstore.swagger.io/?url=' . $currentUrl);
        }

        return new JsonResponse(
            $this->openApi->dump($this->loadOpenApiSchema()), 200, [], true
        );
    }

    public function loadOpenApiSchema(): Root
    {
        if(is_null($this->openApiSchema)) {
            $schema = $this->openApi->extract(json_encode($this->parameterBag->get("draw_open_api.root_schema")));
            $this->openApiSchema = $this->openApi->extract($this->container, $schema);
        }

        return $this->openApiSchema;
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }
}