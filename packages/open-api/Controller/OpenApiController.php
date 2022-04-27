<?php

namespace Draw\Component\OpenApi\Controller;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiController
{
    private OpenApi $openApi;
    private UrlGeneratorInterface $urlGenerator;
    private SchemaBuilderInterface $schemaBuilder;
    private string $sandboxUrl;

    public function __construct(
        OpenApi $openApi,
        SchemaBuilderInterface $schemaBuilder,
        UrlGeneratorInterface $urlGenerator,
        string $sandboxUrl
    ) {
        $this->openApi = $openApi;
        $this->schemaBuilder = $schemaBuilder;
        $this->urlGenerator = $urlGenerator;
        $this->sandboxUrl = $sandboxUrl;
    }

    public function apiDocAction(Request $request, string $version = null): Response
    {
        if ('json' != $request->getRequestFormat()) {
            $parameters = ['_format' => 'json'];
            if ($version) {
                $parameters['version'] = $version;
            }
            $currentRoute = $request->attributes->get('_route');
            $currentUrl = $this->urlGenerator
                ->generate(
                    $currentRoute,
                    $parameters,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

            return new RedirectResponse($this->sandboxUrl.'/index.html?url='.$currentUrl);
        }

        return new JsonResponse(
            $this->openApi->dump($this->schemaBuilder->build($version)),
            200,
            [],
            true
        );
    }
}
