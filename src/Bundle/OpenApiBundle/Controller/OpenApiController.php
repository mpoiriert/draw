<?php namespace Draw\Bundle\OpenApiBundle\Controller;

use Draw\Bundle\OpenApiBundle\Extractor\CacheResourceExtractor;
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
    private $openApiSchema;
    private $cacheResourceExtractor;

    public function __construct(
        OpenApi $openApi,
        ParameterBagInterface $parameterBag,
        ContainerInterface $container,
        CacheResourceExtractor $cacheResourceExtractor
    ) {
        $this->openApi = $openApi;
        $this->parameterBag = $parameterBag;
        $this->container = $container;
        $this->cacheResourceExtractor = $cacheResourceExtractor;
    }

    public function apiDocAction(
        Request $request,
        UrlGeneratorInterface $urlGenerator
    ) {
        if ($request->getRequestFormat() != 'json') {
            $currentRoute = $request->attributes->get('_route');
            $currentUrl = $urlGenerator
                ->generate(
                    $currentRoute,
                    ['_format' => 'json'],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            return new RedirectResponse('http://petstore.swagger.io/?url=' . $currentUrl);
        }

        return new JsonResponse(
            $this->openApi->dump($this->loadOpenApiSchema()), 200, [], true
        );
    }

    public function loadOpenApiSchema(): Root
    {
        if ($this->openApiSchema === null) {
            $debug = $this->parameterBag->get('kernel.debug');
            $path = $this->parameterBag->get('kernel.cache_dir') . '/openApi.php';
            $configCache = new ConfigCache($path, $debug);
            if (!$configCache->isFresh()) {
                $schema = $this->openApi->extract(json_encode($this->parameterBag->get("draw_open_api.root_schema")));
                $openApi = $this->openApi->extract($this->container, $schema);
                $configCache->write(
                    '<?php return unserialize(' . var_export(serialize($openApi), true) . ');',
                    $this->cacheResourceExtractor->getResources()
                );
            }

            $this->openApiSchema = require($path);
        }

        return $this->openApiSchema;
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }
}