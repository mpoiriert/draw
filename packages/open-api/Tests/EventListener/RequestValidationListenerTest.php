<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Configuration\Deserialization;
use Draw\Component\OpenApi\EventListener\RequestValidationListener;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Schema\QueryParameter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @covers \Draw\Component\OpenApi\EventListener\RequestValidationListener
 */
class RequestValidationListenerTest extends TestCase
{
    private RequestValidationListener $object;

    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->object = new RequestValidationListener(
            $this->validator = $this->createMock(ValidatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            [
                KernelEvents::CONTROLLER => ['onKernelController', -5],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testOnKernelControllerNoValidation(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            new Request(),
            null
        );

        $this->validator
            ->expects($this->never())
            ->method('validate');

        $this->object->onKernelController($event);
    }

    public function testOnKernelControllerBodyValidationNoError(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_body_validation', $paramConverter = new Deserialization([]));
        $paramConverter->setName($name = uniqid('name-'));
        $request->attributes->set($name, $bodyObject = (object) []);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($bodyObject, null, ['Default'])
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));

        $this->object->onKernelController($event);
    }

    public function testOnKernelControllerQueryParametersValidationNoError(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_query_parameters_validation', [$queryParameter = new QueryParameter()]);

        $queryParameter->name = $name = uniqid('name-');
        $request->attributes->set($name, $parameterObject = (object) []);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($parameterObject, [], null)
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));

        $this->object->onKernelController($event);
    }

    public function testOnKernelControllerDoNotValidate(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_body_validation', $paramConverter = new Deserialization([]));
        $paramConverter->setName($name = uniqid('name-'));
        $paramConverter->setOptions(['validate' => false]);
        $request->attributes->set($name, (object) []);

        $this->validator
            ->expects($this->never())
            ->method('validate');

        $this->object->onKernelController($event);
    }

    public function testOnKernelControllerWithError(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_body_validation', $paramConverter = new Deserialization([]));
        $paramConverter->setName($name = uniqid('name-'));
        $paramConverter->setOptions(['validator' => ['Groups' => $groups = [uniqid('group-')]]]);
        $request->attributes->set($name, $bodyObject = (object) []);

        $request->attributes->set('_draw_query_parameters_validation', [$queryParameter = new QueryParameter()]);
        $queryParameter->name = $name = uniqid('name-');
        $request->attributes->set($name, $parameterObject = (object) []);
        $queryParameter->constraints = [[(object) []]];

        $this->validator
            ->expects($this->exactly(2))
            ->method('validate')
            ->withConsecutive(
                [$bodyObject, null, $groups],
                [$parameterObject, $queryParameter->constraints]
            )
            ->willReturnOnConsecutiveCalls(
                $bodyViolationList = new ConstraintViolationList(),
                $parameterViolationList = new ConstraintViolationList(),
            );

        $bodyViolationList->add(
            $originalBodyViolation = new ConstraintViolation(
                uniqid('message-'),
                uniqid('template-'),
                [uniqid('parameter-1-')],
                null,
                'attribute',
                null,
            )
        );

        $parameterViolationList->add(
            $originalParameterViolation = new ConstraintViolation(
                uniqid('message-'),
                uniqid('template-'),
                [uniqid('parameter-1-')],
                null,
                null,
                null,
            )
        );

        try {
            $this->object->onKernelController($event);
            $this->fail('Expect exception of type: '.ConstraintViolationListException::class);
        } catch (ConstraintViolationListException $error) {
            $violationList = $error->getViolationList();

            $this->assertViolationIsSimilar(
                $originalBodyViolation,
                $violationList->get(0),
                '$.body.'.$originalBodyViolation->getPropertyPath()
            );

            $this->assertViolationIsSimilar(
                $originalParameterViolation,
                $violationList->get(1),
                '$.query.'.$queryParameter->name
            );
        }
    }

    private function assertViolationIsSimilar(
        ConstraintViolationInterface $originalViolation,
        ConstraintViolationInterface $newViolation,
        string $newPropertyPath
    ): void {
        $this->assertSame(
            $originalViolation->getMessage(),
            $newViolation->getMessage()
        );

        $this->assertSame(
            $originalViolation->getMessageTemplate(),
            $newViolation->getMessageTemplate()
        );

        $this->assertSame(
            $originalViolation->getParameters(),
            $newViolation->getParameters()
        );

        $this->assertSame(
            $originalViolation->getRoot(),
            $newViolation->getRoot()
        );

        $this->assertSame(
            $originalViolation->getInvalidValue(),
            $newViolation->getInvalidValue()
        );

        $this->assertSame(
            $originalViolation->getPlural(),
            $newViolation->getPlural()
        );

        $this->assertSame(
            $originalViolation->getCode(),
            $newViolation->getCode()
        );

        $this->assertSame(
            $newPropertyPath,
            $newViolation->getPropertyPath()
        );
    }
}