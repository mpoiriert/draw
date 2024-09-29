<?php

namespace Draw\Component\OpenApi\Tests\Controller;

use Draw\Component\OpenApi\Controller\OpenApiController;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
    private OpenApiController $object;

    /**
     * @var OpenApi&MockObject
     */
    private OpenApi $openApi;

    /**
     * @var SchemaBuilderInterface&MockObject
     */
    private SchemaBuilderInterface $schemaBuilder;

    /**
     * @var UrlGeneratorInterface&MockObject
     */
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
            ->expects(static::never())
            ->method('dump')
        ;

        $route = uniqid('route-');

        $this->urlGenerator
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

        $response = $this->object->apiDocAction($request);

        static::assertInstanceOf(RedirectResponse::class, $response);

        static::assertSame(
            $this->sandboxUrl.'/index.html?url='.$url,
            $response->getTargetUrl()
        );
    }

    public function testApiDocActionVersioned(): void
    {
        $route = uniqid('route-');
        $version = uniqid('version-');

        $this->urlGenerator
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

        $this->object->apiDocAction($request, $version);
    }

    public function testApiDocActionJson(): void
    {
        $version = uniqid('version-');

        $this->schemaBuilder
            ->expects(static::once())
            ->method('build')
            ->with(
                static::isInstanceOf(ExtractionContextInterface::class)
            )
            ->willReturn($rootSchema = new Root())
        ;

        $this->openApi
            ->expects(static::once())
            ->method('dump')
            ->with($rootSchema)
            ->willReturn($rootSchemaJson = json_encode(['version' => $version], \JSON_THROW_ON_ERROR))
        ;

        $this->urlGenerator
            ->expects(static::never())
            ->method('generate')
        ;

        $request = new Request();
        $request->setRequestFormat('json');

        $response = $this->object->apiDocAction($request, $version);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(200, $response->getStatusCode());
        static::assertSame($rootSchemaJson, $response->getContent());
    }
}
