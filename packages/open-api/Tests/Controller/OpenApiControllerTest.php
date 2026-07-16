<?php

namespace Draw\Component\OpenApi\Tests\Controller;

use Draw\Component\OpenApi\Controller\OpenApiController;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
class OpenApiControllerTest extends TestCase
{
    private string $sandboxUrl;

    protected function setUp(): void
    {
        $this->sandboxUrl = uniqid('/path/').'/sandbox';
    }

    public function testApiDocAction(): void
    {
        $object = new OpenApiController(
            $openApi = $this->createMock(OpenApi::class),
            static::createStub(SchemaBuilderInterface::class),
            $urlGenerator = $this->createMock(UrlGeneratorInterface::class),
            $this->sandboxUrl
        );

        $openApi
            ->expects(static::never())
            ->method('dump')
        ;

        $route = uniqid('route-');

        $urlGenerator
            ->expects(static::once())
            ->method('generate')
            ->with(
                $route,
                [
                    '_format' => 'json',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($url = uniqid('url-'))
        ;

        $request = new Request();
        $request->attributes->set('_route', $route);

        $response = $object->apiDocAction($request);

        static::assertInstanceOf(RedirectResponse::class, $response);

        static::assertSame(
            $this->sandboxUrl.'/index.html?url='.$url,
            $response->getTargetUrl()
        );
    }

    public function testApiDocActionVersioned(): void
    {
        $object = new OpenApiController(
            static::createStub(OpenApi::class),
            static::createStub(SchemaBuilderInterface::class),
            $urlGenerator = $this->createMock(UrlGeneratorInterface::class),
            $this->sandboxUrl
        );

        $route = uniqid('route-');
        $version = uniqid('version-');

        $urlGenerator
            ->expects(static::once())
            ->method('generate')
            ->with(
                $route,
                [
                    '_format' => 'json',
                    'version' => $version,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn(uniqid('url-'))
        ;

        $request = new Request();
        $request->attributes->set('_route', $route);

        $object->apiDocAction($request, $version);
    }

    public function testApiDocActionJson(): void
    {
        $object = new OpenApiController(
            $openApi = $this->createMock(OpenApi::class),
            $schemaBuilder = $this->createMock(SchemaBuilderInterface::class),
            $urlGenerator = $this->createMock(UrlGeneratorInterface::class),
            $this->sandboxUrl
        );

        $version = uniqid('version-');

        $schemaBuilder
            ->expects(static::once())
            ->method('build')
            ->with(
                static::isInstanceOf(ExtractionContextInterface::class)
            )
            ->willReturn($rootSchema = new Root())
        ;

        $openApi
            ->expects(static::once())
            ->method('dump')
            ->with($rootSchema)
            ->willReturn($rootSchemaJson = json_encode(['version' => $version], \JSON_THROW_ON_ERROR))
        ;

        $urlGenerator
            ->expects(static::never())
            ->method('generate')
        ;

        $request = new Request();
        $request->setRequestFormat('json');

        $response = $object->apiDocAction($request, $version);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(200, $response->getStatusCode());
        static::assertSame($rootSchemaJson, $response->getContent());
    }
}
