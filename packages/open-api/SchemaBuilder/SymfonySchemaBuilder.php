<?php

namespace Draw\Component\OpenApi\SchemaBuilder;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

class SymfonySchemaBuilder implements SchemaBuilderInterface
{
    /**
     * @var Root[]
     */
    private array $openApiSchemas = [];

    public function __construct(
        private OpenApi $openApi,
        private RouterInterface $router,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function build(ExtractionContextInterface $extractionContext): Root
    {
        $key = $extractionContext->getCacheKey();

        if (!isset($this->openApiSchemas[$key])) {
            $this->openApiSchemas[$key] = $this->doBuild($extractionContext);
        }

        return $this->openApiSchemas[$key];
    }

    private function doBuild(ExtractionContextInterface $extractionContext): Root
    {
        $schema = $extractionContext->getRootSchema();
        $extractionContext->setParameter('api.cacheable', false);

        $this->openApi->extract(
            json_encode($this->parameterBag->get('draw_open_api.root_schema'), \JSON_THROW_ON_ERROR),
            $schema,
            $extractionContext
        );

        if (!isset($schema->vendor['X-DrawOpenApi-FromCache'])) {
            $extractionContext->setParameter('api.cacheable', true);
            $this->openApi->extract($this->router, $schema, $extractionContext);
        }

        if ($schema->getVendorData()) {
            $data = [
                ['', 'Metadata', '', ''],
                ['', '---', '---', ''],
            ];
            foreach ($schema->getVendorData() as $key => $value) {
                $data[] = ['', $key, $value, ''];
            }

            foreach ($data as $row) {
                $schema->info->description = $schema->info->description."\n".implode('|', $row);
            }
        }

        $schema->vendor = [];

        return $schema;
    }
}
