<?php

namespace Draw\Component\OpenApi\Tests\Controller;

use Draw\Component\OpenApi\Controller\OpenApiController;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiControllerTest extends TestCase
{
    private OpenApiController $object;

    private OpenApi $openApi;

    private SchemaBuilderInterface $schemaBuilder;

    private UrlGeneratorInterface $urlGenerator;

    private string $sandboxUrl;

    protected function setUp(): void
    {
        $this->object = new OpenApiController(
            $this->openApi = $this->createMock(OpenApi::class),
            $this->schemaBuilder = $this->createMock(SchemaBuilderInterface::class),
            $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class),
            $this->sandboxUrl = uniqid('/path/').'/sandbox'
        );
    }

    public function testApiDocAction(): void
    {
        $this->openApi
            ->expects($this->never())
            ->method('dump');

        $route = uniqid('route-');

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $route,
                [
                    '_format' => 'json',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($url = uniqid('url-'));

        $request = new Request();
        $request->attributes->set('_route', $route);

        $response = $this->object->apiDocAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame(
            $this->sandboxUrl.'/index.html?url='.$url,
            $response->getTargetUrl()
        );
    }

    public function testApiDocActionVersioned(): void
    {
        $route = uniqid('route-');
        $version = uniqid('version-');

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $route,
                [
                    '_format' => 'json',
                    'version' => $version,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn(uniqid('url-'));

        $request = new Request();
        $request->attributes->set('_route', $route);

        $this->object->apiDocAction($request, $version);
    }

    public function testApiDocActionJson(): void
    {
        $version = uniqid('version-');

        $this->schemaBuilder
            ->expects($this->once())
            ->method('build')
            ->with($version)
            ->willReturn($rootSchema = new Root());

        $this->openApi
            ->expects($this->once())
            ->method('dump')
            ->with($rootSchema)
            ->willReturn($rootSchemaJson = json_encode(['version' => $version]));

        $this->urlGenerator
            ->expects($this->never())
            ->method('generate');

        $request = new Request();
        $request->setRequestFormat('json');

        $response = $this->object->apiDocAction($request, $version);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($rootSchemaJson, $response->getContent());
    }
}
