<?php

namespace Draw\Component\OpenApi\Controller;

use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiController
{
    public function __construct(
        private OpenApi $openApi,
        private SchemaBuilderInterface $schemaBuilder,
        private UrlGeneratorInterface $urlGenerator,
        private string $sandboxUrl
    ) {
    }

    public function apiDocAction(Request $request, ?string $version = null): Response
    {
        $scope = $request->query->get('scope');

        if ('json' != $request->getRequestFormat()) {
            $parameters = ['_format' => 'json'];
            if ($version) {
                $parameters['version'] = $version;
            }

            if ($scope) {
                $parameters['scope'] = $scope;
            }

            $currentRoute = $request->attributes->get('_route');
            $currentUrl = $this->urlGenerator
                ->generate(
                    $currentRoute,
                    $parameters,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

            return new RedirectResponse($this->sandboxUrl.'/index.html?url='.urlencode($currentUrl));
        }

        $extractionContext = new ExtractionContext($this->openApi);

        $extractionContext->setParameter('api.version', $version);
        $extractionContext->setParameter('api.scope', $scope);

        return new JsonResponse(
            $this->openApi->dump(
                $this->schemaBuilder->build($extractionContext),
                true,
                $extractionContext
            ),
            200,
            [],
            true
        );
    }
}
