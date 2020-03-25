<?php namespace Draw\Bundle\OpenApiBundle\Controller;

use Draw\Component\OpenApi\OpenApi;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiController
{
    public function apiDocAction(
        OpenApi $openApi,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $parameterBag,
        ContainerInterface $container
    ) {
        if ($request->getRequestFormat() != 'json') {
            $currentRoute = $request->attributes->get('_route');
            $currentUrl = $urlGenerator->generate($currentRoute, array('_format' => 'json'), true);
            return new RedirectResponse('http://petstore.swagger.io/?url=' . $currentUrl);
        }

        $schema = $openApi->extract(json_encode($parameterBag->get("draw_open_api.root_schema")));
        $schema = $openApi->extract($container, $schema);
        $jsonSchema = $openApi->dump($schema);

        return new JsonResponse($jsonSchema, 200, [], true);
    }
}