<?php

namespace Draw\Component\OpenApi\SchemaBuilder;

use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

class SymfonySchemaBuilder implements SchemaBuilderInterface
{
    /**
     * @var array|Root[]
     */
    private array $openApiSchemas = [];

    private ParameterBagInterface $parameterBag;
    private RouterInterface $router;
    private OpenApi $openApi;

    public function __construct(
        OpenApi $openApi,
        RouterInterface $router,
        ParameterBagInterface $parameterBag
    ) {
        $this->openApi = $openApi;
        $this->parameterBag = $parameterBag;
        $this->router = $router;
    }

    public function build(?string $version = null): Root
    {
        $versionKey = $version ?: '~';
        if (!isset($this->openApiSchemas[$versionKey])) {
            $this->openApiSchemas[$versionKey] = $this->doBuild($version);
        }

        return $this->openApiSchemas[$versionKey];
    }

    private function doBuild(?string $version = null): Root
    {
        $extractionContext = new ExtractionContext($this->openApi, $schema = new Root());
        $extractionContext->setParameter('api.cacheable', false);
        $extractionContext->setParameter('api.version', $version);

        $this->openApi->extract(json_encode($this->parameterBag->get('draw_open_api.root_schema')), $schema);

        if (!isset($schema->vendor['X-DrawOpenApi-FromCache'])) {
            $extractionContext->setParameter('api.cacheable', true);
            $this->openApi->extract($this->router, $schema, $extractionContext);
        }

        return $schema;
    }
}
