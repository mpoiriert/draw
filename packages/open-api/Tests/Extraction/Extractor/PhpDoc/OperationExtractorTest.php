<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\PhpDoc;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\PhpDoc\OperationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class OperationExtractorTest extends TestCase
{
    use MockTrait;

    private OperationExtractor $phpDocOperationExtractor;

    protected function setUp(): void
    {
        $this->phpDocOperationExtractor = new OperationExtractor();
    }

    #[DataProvider('provideCanExtractCases')]
    public function testCanExtract(mixed $source, mixed $type, bool $canBeExtract): void
    {
        static::assertSame(
            $canBeExtract,
            $this->phpDocOperationExtractor->canExtract(
                $source,
                $type,
                $context = $this->createMock(ExtractionContextInterface::class)
            )
        );

        if ($canBeExtract) {
            return;
        }

        $this->expectException(ExtractionImpossibleException::class);
        $this->phpDocOperationExtractor->extract($source, $type, $context);
    }

    public static function provideCanExtractCases(): iterable
    {
        $reflectionMethod = new \ReflectionMethod(__NAMESPACE__.'\PhpDocOperationExtractorStubService', 'operation');

        return [
            [null, null, false],
            [null, new Operation(), false],
            [$reflectionMethod, null, false],
            [$reflectionMethod, new Operation(), true],
        ];
    }

    public function testExtract(): void
    {
        $this->phpDocOperationExtractor->registerExceptionResponseCodes(
            ExtractionImpossibleException::class,
            400
        );
        $this->phpDocOperationExtractor->registerExceptionResponseCodes('LengthException', 408, 'Define message');

        $context = $this->extractStubServiceMethod('operation');

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtractVoid(): void
    {
        $context = $this->extractStubServiceMethod('void');

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_void.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtractDefaultVoid(): void
    {
        $context = $this->extractStubServiceMethod('defaultVoid');

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_defaultVoid.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtractArrayOfPrimitive(): void
    {
        $context = $this->extractStubServiceMethod('arrayOfPrimitive');

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_arrayOfPrimitive.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtractGenericCollection(): void
    {
        $context = $this->extractStubServiceMethod('genericCollection');

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_genericCollection.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtractInvalidTypeParameter(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No type found for parameter named [param] for operation id [operation_id]');

        $operation = new Operation();
        $operation->operationId = 'operation_id';
        $operation->parameters[] = $parameter = new QueryParameter();
        $parameter->name = 'param';

        $this->extractStubServiceMethod('invalidTypeParameter', $operation);
    }

    private function extractStubServiceMethod(string $method, ?Operation $operation = null): ExtractionContextInterface
    {
        $reflectionMethod = new \ReflectionMethod(__NAMESPACE__.'\PhpDocOperationExtractorStubService', $method);

        $context = $this->getExtractionContext([new TypeSchemaExtractor()]);
        $schema = $context->getRootSchema();
        $schema->paths['/service'] = $pathItem = new PathItem();

        $pathItem->get = $operation = $operation ?: new Operation();

        $this->phpDocOperationExtractor->extract($reflectionMethod, $operation, $context);

        return $context;
    }

    public function getExtractionContext(array $extractors = []): ExtractionContext
    {
        $openApi = new OpenApi($extractors);
        $schema = $openApi->extract('{"swagger":"2.0","definitions":{}}');

        return new ExtractionContext($openApi, $schema);
    }
}

/**
 * @template T of mixed
 */
class PhpDocOperationExtractorStubClass
{
}

/**
 * This class is a stub and the code implementation make no sens, just the doc is useful.
 */
class PhpDocOperationExtractorStubService
{
    /**
     * @return PhpDocOperationExtractorStubService
     *
     * @throws \Exception                    When problem occur
     * @throws \LengthException
     * @throws ExtractionImpossibleException
     */
    public function operation(self $service, mixed $string, array $array)
    {
        if ($string) {
            throw new ExtractionImpossibleException();
        }

        return $service;
    }

    /**
     * @return void Does not return value
     */
    public function void(): void
    {
    }

    public function defaultVoid(): void
    {
    }

    /**
     * @return int[]
     */
    public function arrayOfPrimitive()
    {
        return [];
    }

    /**
     * @return PhpDocOperationExtractorStubClass<int>
     */
    public function genericCollection(): PhpDocOperationExtractorStubClass
    {
        return new PhpDocOperationExtractorStubClass();
    }

    /**
     * @param toto $param
     */
    public function invalidTypeParameter($param): void
    {
    }
}
