<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\PhpDoc;

use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\Extractor\PhpDoc\OperationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Exception;
use LengthException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

class OperationExtractorTest extends TestCase
{
    /**
     * @var OperationExtractor
     */
    private $phpDocOperationExtractor;

    public function setUp()
    {
        $this->phpDocOperationExtractor = new OperationExtractor();
    }

    public function provideTestCanExtract()
    {
        $reflectionMethod = new ReflectionMethod(__NAMESPACE__.'\PhpDocOperationExtractorStubService', 'operation');

        return [
            [null, null, false],
            [null, new Operation(), false],
            [$reflectionMethod, null, false],
            [$reflectionMethod, new Operation(), true],
        ];
    }

    /**
     * @dataProvider provideTestCanExtract
     *
     * @param $source
     * @param $type
     * @param $canBeExtract
     */
    public function testCanExtract($source, $type, $canBeExtract)
    {
        /** @var ExtractionContextInterface $context */
        $context = $this->getMockForAbstractClass(ExtractionContextInterface::class);

        $this->assertSame($canBeExtract, $this->phpDocOperationExtractor->canExtract($source, $type, $context));

        if (!$canBeExtract) {
            try {
                $this->phpDocOperationExtractor->extract($source, $type, $context);
                $this->fail('should throw a exception of type [Draw\Component\OpenApi\Extraction\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testExtract()
    {
        $this->phpDocOperationExtractor->registerExceptionResponseCodes(
            'Draw\Component\OpenApi\Extraction\ExtractionImpossibleException',
            400
        );
        $this->phpDocOperationExtractor->registerExceptionResponseCodes('LengthException', 408, 'Define message');

        $context = $this->extractStubServiceMethod('operation');

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtract_void()
    {
        $context = $this->extractStubServiceMethod('void');

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_void.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtract_defaultVoid()
    {
        $context = $this->extractStubServiceMethod('defaultVoid');

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_defaultVoid.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtract_arrayOfPrimitive()
    {
        $context = $this->extractStubServiceMethod('arrayOfPrimitive');

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_arrayOfPrimitive.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtract_genericCollection()
    {
        $context = $this->extractStubServiceMethod('genericCollection');

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/phpDocOperationExtractorExtract_testExtract_genericCollection.json'),
            $context->getOpenApi()->dump($context->getRootSchema(), false)
        );
    }

    public function testExtract_invalidTypeParameter()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No type found for parameter named [param] for operation id [operation_id]');

        $operation = new Operation();
        $operation->operationId = 'operation_id';
        $operation->parameters[] = $parameter = new QueryParameter();
        $parameter->name = 'param';

        $this->extractStubServiceMethod('invalidTypeParameter', $operation);
    }

    private function extractStubServiceMethod(string $method, Operation $operation = null)
    {
        $reflectionMethod = new ReflectionMethod(__NAMESPACE__.'\PhpDocOperationExtractorStubService', $method);

        $context = $this->getExtractionContext();
        $context->getOpenApi()->registerExtractor(new TypeSchemaExtractor());
        $schema = $context->getRootSchema();
        $schema->paths['/service'] = $pathItem = new PathItem();

        $pathItem->get = $operation = $operation ?: new Operation();

        $this->phpDocOperationExtractor->extract($reflectionMethod, $operation, $context);

        return $context;
    }

    public function getExtractionContext()
    {
        $openApi = new OpenApi();
        $schema = $openApi->extract('{"swagger":"2.0","definitions":{}}');

        return new ExtractionContext($openApi, $schema);
    }
}

class PhpDocOperationExtractorStubClass
{
}

/**
 * This class is a stub and the code implementation make no sens, just the doc is usefull.
 */
class PhpDocOperationExtractorStubService
{
    /**
     * @param $string
     *
     * @return PhpDocOperationExtractorStubService
     *
     * @throws Exception                     When problem occur
     * @throws LengthException
     * @throws ExtractionImpossibleException
     */
    public function operation(PhpDocOperationExtractorStubService $service, $string, array $array)
    {
        if ($string) {
            throw new ExtractionImpossibleException();
        }

        return $service;
    }

    /**
     * @return void Does not return value
     */
    public function void()
    {
    }

    public function defaultVoid()
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
    public function genericCollection()
    {
        return new PhpDocOperationExtractorStubClass();
    }

    /**
     * @param toto $param
     */
    public function invalidTypeParameter($param)
    {

    }
}
