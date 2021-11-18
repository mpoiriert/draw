<?php

namespace Draw\Bundle\OpenApiBundle\Controller;

use Draw\Bundle\OpenApiBundle\Extractor\CacheResourceExtractor;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\ConfigCache;
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
    /**
     * @var array|Root[]
     */
    private $openApiSchemas = [];
    private $cacheResourceExtractor;
    private $urlGenerator;

    public function __construct(
        OpenApi $openApi,
        ParameterBagInterface $parameterBag,
        ContainerInterface $container,
        CacheResourceExtractor $cacheResourceExtractor,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->openApi = $openApi;
        $this->parameterBag = $parameterBag;
        $this->container = $container;
        $this->cacheResourceExtractor = $cacheResourceExtractor;
        $this->urlGenerator = $urlGenerator;
    }

    public function apiDocAction(
        Request $request,
        string $version = null
    ) {
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

            return new RedirectResponse('/bundles/drawopenapi/sandbox/index.html?url='.$currentUrl);
        }

        return new JsonResponse(
            $this->openApi->dump($this->loadOpenApiSchema($version)), 200, [], true
        );
    }

    public function loadOpenApiSchema(string $version = null): Root
    {
        $versionKey = $version ?: '~';
        if (!isset($this->openApiSchemas[$versionKey])) {
            $debug = $this->parameterBag->get('kernel.debug');
            $path = $this->parameterBag->get('kernel.cache_dir').'/openApi-'.$versionKey.'.php';
            $configCache = new ConfigCache($path, $debug);
            if (!$configCache->isFresh()) {
                $schema = $this->openApi->extract(json_encode($this->parameterBag->get('draw_open_api.root_schema')));
                $extractionContext = new ExtractionContext($this->openApi, $schema);
                if ($version) {
                    $schema->info->version = $version;
                    $extractionContext->setParameter(
                        PropertiesExtractor::CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY,
                        true
                    );

                    if ($this->parameterBag->has('draw_open_api.versions')) {
                        $description = $schema->info->description;
                        $schema->info->description = '';
                        $versions = $this->parameterBag->get('draw_open_api.versions');
                        foreach ($versions as $otherVersion) {
                            if ((string) $otherVersion === $version) {
                                continue;
                            }

                            $otherVersionUrl = $this->urlGenerator
                                ->generate(
                                    'draw_open_api.versioned_api_doc',
                                    ['version' => $otherVersion],
                                    UrlGeneratorInterface::ABSOLUTE_URL
                                );

                            $schema->info->description .= 'Go to <a href="'.$otherVersionUrl.'">Version '.$otherVersion.'</a><br/>';
                        }

                        $schema->info->description .= $description;
                    }
                }

                $openApi = $this->openApi->extract($this->container, $schema, $extractionContext);
                $configCache->write(
                    '<?php return unserialize('.var_export(serialize($openApi), true).');',
                    $this->cacheResourceExtractor->getResources()
                );
            }

            $this->openApiSchemas[$versionKey] = require $path;
        }

        return $this->openApiSchemas[$versionKey];
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }
}
