<?php

namespace Draw\Component\OpenApi\Tests\Request\ParamConverter;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\OpenApi\Request\ParamConverter\DeserializeBodyParamConverter;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\LogicException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * @covers \Draw\Component\OpenApi\Request\ParamConverter\DeserializeBodyParamConverter
 */
class DeserializeBodyParamConverterTest extends TestCase
{
    private DeserializeBodyParamConverter $object;

    /**
     * @var SerializerInterface&MockObject
     */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->object = new DeserializeBodyParamConverter(
            $this->serializer = $this->createMock(SerializerInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ParamConverterInterface::class,
            $this->object
        );
    }

    public function testSupportsNoClass(): void
    {
        $paramConverter = new ParamConverter();
        $paramConverter->setConverter('draw_open_api.request_body');

        static::assertFalse($this->object->supports($paramConverter));
    }

    public function testSupportsWrongConverter(): void
    {
        $paramConverter = new ParamConverter();
        $paramConverter->setClass(uniqid('class-'));

        static::assertFalse($this->object->supports($paramConverter));
    }

    public function testSupports(): void
    {
        $paramConverter = new ParamConverter();
        $paramConverter->setConverter('draw_open_api.request_body');
        $paramConverter->setClass(uniqid('class-'));

        static::assertTrue($this->object->supports($paramConverter));
    }

    public function testApplyRequestUnsupportedFormat(): void
    {
        $paramConverter = $this->createParamConverter();

        $request = $this->createRequest('', 'application/xml');

        $this->serializer
            ->expects(static::never())
            ->method('deserialize');

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->expectExceptionMessage('Unsupported request Content-Type [application/xml]');

        $this->object->apply($request, $paramConverter);
    }

    public function testApplySerializerUnsupportedFormat(): void
    {
        $paramConverter = $this->createParamConverter();

        $request = $this->createRequest('{}');

        $this->serializer
            ->expects(static::once())
            ->method('deserialize')
            ->with(
                '{}',
                $paramConverter->getClass(),
                'json',
                static::isInstanceOf(DeserializationContext::class)
            )
            ->willThrowException($exception = new UnsupportedFormatException(uniqid('message-')));

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->expectExceptionMessage($exception->getMessage());

        $this->object->apply($request, $paramConverter);
    }

    public function testApplySerializerMultipartFormData(): void
    {
        $paramConverter = $this->createParamConverter();

        $request = $this->createRequest('{}', 'multipart/form-data');
        $request->request->set('key', 'value');

        $this->serializer
            ->expects(static::once())
            ->method('deserialize')
            ->with(
                '{"key":"value"}',
                $paramConverter->getClass(),
                'json',
                static::isInstanceOf(DeserializationContext::class)
            )
            ->willReturn((object) []);

        $this->object->apply($request, $paramConverter);
    }

    public function testApplySerializerError(): void
    {
        $paramConverter = $this->createParamConverter();

        $request = $this->createRequest('{}');

        $this->serializer
            ->expects(static::once())
            ->method('deserialize')
            ->willThrowException($exception = new LogicException(uniqid('message-')));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage($exception->getMessage());

        $this->object->apply($request, $paramConverter);
    }

    public function testApply(): void
    {
        $paramConverter = $this->createParamConverter();

        $request = $this->createRequest('{}');

        $this->serializer
            ->expects(static::once())
            ->method('deserialize')
            ->willReturn($object = (object) []);

        $this->object->apply($request, $paramConverter);

        static::assertSame(
            $object,
            $request->attributes->get($paramConverter->getName())
        );

        static::assertSame(
            $paramConverter,
            $request->attributes->get('_draw_body_validation')
        );
    }

    public function provideTestApplyAssignPropertiesFromAttribute(): iterable
    {
        yield 'simple' => [
            ['id' => 'id'],
            ['id' => $id = uniqid('id-')],
            ['id' => $id],
        ];

        yield 'simple-existing' => [
            ['id' => 'id'],
            ['id' => $id = uniqid('id-')],
            ['name' => $name = uniqid('name'), 'id' => $id],
            json_encode(['name' => $name]),
        ];

        yield 'empty-body' => [
            ['id' => 'id'],
            ['id' => $id = uniqid('id-')],
            ['id' => $id],
            '',
        ];

        yield 'deep-target' => [
            ['subObject.id' => 'id'],
            ['id' => $id = uniqid('id-')],
            ['subObject' => ['id' => $id]],
        ];

        yield 'deep-source' => [
            ['id' => 'subObject.id'],
            ['subObject' => (object) ['id' => $id = uniqid('id-')]],
            ['id' => $id],
        ];
    }

    /**
     * @dataProvider provideTestApplyAssignPropertiesFromAttribute
     */
    public function testApplyAssignPropertiesFromAttribute(
        array $propertiesMap,
        array $requestAttributes,
        array $expectedData,
        ?string $requestContent = '{}'
    ): void {
        $paramConverter = $this->createParamConverter();
        $paramConverter->setOptions(['propertiesMap' => $propertiesMap]);

        $request = $this->createRequest($requestContent);
        $request->attributes->add($requestAttributes);

        $this->serializer
            ->expects(static::once())
            ->method('deserialize')
            ->with(json_encode($expectedData))
            ->willReturn((object) []);

        $this->object->apply($request, $paramConverter);
    }

    public function testApplyConfigureContext(): void
    {
        $paramConverter = $this->createParamConverter();
        $paramConverter->setOptions([
            'deserializationContext' => $options = [
                'groups' => [uniqid('group-')],
                'version' => uniqid('version-'),
                'enableMaxDepth' => true,
                'other' => uniqid('value'),
            ],
        ]);

        $request = $this->createRequest('{}');

        $this->serializer
            ->expects(static::once())
            ->method('deserialize')
            ->with(
                static::isType('string'),
                static::isType('string'),
                'json',
                static::callback(function (DeserializationContext $context) use ($options) {
                    $this->assertSame(
                        $options['groups'],
                        $context->getAttribute('groups')
                    );

                    $this->assertSame(
                        $options['version'],
                        $context->getAttribute('version')
                    );

                    $this->assertTrue($context->getAttribute('max_depth_checks'));

                    $this->assertSame(
                        $options['other'],
                        $context->getAttribute('other')
                    );

                    return true;
                })
            )
            ->willReturn((object) []);

        $this->object->apply($request, $paramConverter);
    }

    private function createRequest(string $content, string $contentType = 'application/json'): Request
    {
        $request = new Request();
        ReflectionAccessor::setPropertyValue(
            $request,
            'content',
            $content
        );

        $request->headers->set('Content-Type', $contentType);

        return $request;
    }

    private function createParamConverter(): ParamConverter
    {
        $paramConverter = new ParamConverter();
        $paramConverter->setName(uniqid('name-'));
        $paramConverter->setConverter('draw_open_api.request_body');
        $paramConverter->setClass(uniqid('class-'));

        return $paramConverter;
    }
}
