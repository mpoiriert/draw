<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\EventListener\RequestValidationListener;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Draw\Component\Tester\DoubleTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[CoversClass(RequestValidationListener::class)]
class RequestValidationListenerTest extends TestCase
{
    use DoubleTrait;

    public function testSubscribedEvents(): void
    {
        $object = new RequestValidationListener(
            static::createStub(ValidatorInterface::class)
        );

        static::assertSame(
            [
                KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelController', -5],
            ],
            $object::getSubscribedEvents()
        );
    }

    public function testOnKernelControllerNoValidation(): void
    {
        $object = new RequestValidationListener(
            $validator = $this->createMock(ValidatorInterface::class)
        );

        $event = new ControllerArgumentsEvent(
            static::createStub(HttpKernelInterface::class),
            'gettype',
            [],
            new Request(),
            null
        );

        $validator
            ->expects(static::never())
            ->method('validate')
            ->seal()
        ;

        $object->onKernelController($event);
    }

    public function testOnKernelControllerBodyValidationNoError(): void
    {
        $object = new RequestValidationListener(
            $validator = $this->createMock(ValidatorInterface::class)
        );

        $event = new ControllerArgumentsEvent(
            static::createStub(HttpKernelInterface::class),
            'gettype',
            [],
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_body_validation', $requestBody = new RequestBody());
        $requestBody->argumentName = $name = uniqid('name-');
        $request->attributes->set($name, $bodyObject = (object) []);

        $validator
            ->expects(static::once())
            ->method('validate')
            ->with($bodyObject, null, ['Default'])
            ->willReturn(static::createStub(ConstraintViolationListInterface::class))
            ->seal()
        ;

        $object->onKernelController($event);
    }

    public function testOnKernelControllerQueryParametersValidationNoError(): void
    {
        $object = new RequestValidationListener(
            $validator = $this->createMock(ValidatorInterface::class)
        );

        $event = new ControllerArgumentsEvent(
            static::createStub(HttpKernelInterface::class),
            'gettype',
            [],
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_query_parameters_validation', [$queryParameter = new QueryParameter()]);

        $queryParameter->name = $name = uniqid('name-');
        $request->attributes->set($name, $parameterObject = (object) []);

        $validator
            ->expects(static::once())
            ->method('validate')
            ->with($parameterObject, [], null)
            ->willReturn(static::createStub(ConstraintViolationListInterface::class))
            ->seal()
        ;

        $object->onKernelController($event);
    }

    public function testOnKernelControllerDoNotValidate(): void
    {
        $object = new RequestValidationListener(
            $validator = $this->createMock(ValidatorInterface::class)
        );

        $event = new ControllerArgumentsEvent(
            static::createStub(HttpKernelInterface::class),
            'gettype',
            [],
            $request = new Request(),
            null
        );

        $request->attributes->set('_draw_body_validation', $requestBody = new RequestBody(validate: false));
        $requestBody->argumentName = $name = uniqid('name-');

        $request->attributes->set($name, (object) []);

        $validator
            ->expects(static::never())
            ->method('validate')
            ->seal()
        ;

        $object->onKernelController($event);
    }

    public function testOnKernelControllerWithError(): void
    {
        $object = new RequestValidationListener(
            $validator = $this->createMock(ValidatorInterface::class)
        );

        $event = new ControllerArgumentsEvent(
            static::createStub(HttpKernelInterface::class),
            'gettype',
            [],
            $request = new Request(),
            null
        );

        $request->attributes->set(
            '_draw_body_validation',
            $requestBody = new RequestBody(validationGroups: $groups = [uniqid('group-')])
        );
        $requestBody->argumentName = $name = uniqid('name-');

        $request->attributes->set($name, $bodyObject = (object) []);

        $request->attributes->set('_draw_query_parameters_validation', [$queryParameter = new QueryParameter()]);
        $queryParameter->name = $name = uniqid('name-');
        $request->attributes->set($name, $parameterObject = (object) []);
        $queryParameter->constraints = [new NotNull()];

        $validator
            ->expects(static::exactly(2))
            ->method('validate')
            ->with(
                ...static::withConsecutive(
                    [$bodyObject, null, $groups],
                    [$parameterObject, $queryParameter->constraints, null]
                )
            )
            ->willReturnOnConsecutiveCalls(
                $bodyViolationList = new ConstraintViolationList(),
                $parameterViolationList = new ConstraintViolationList(),
            )
            ->seal()
        ;

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
            $object->onKernelController($event);
            static::fail('Expect exception of type: '.ConstraintViolationListException::class);
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
        string $newPropertyPath,
    ): void {
        static::assertSame(
            $originalViolation->getMessage(),
            $newViolation->getMessage()
        );

        static::assertSame(
            $originalViolation->getMessageTemplate(),
            $newViolation->getMessageTemplate()
        );

        static::assertSame(
            $originalViolation->getParameters(),
            $newViolation->getParameters()
        );

        static::assertSame(
            $originalViolation->getRoot(),
            $newViolation->getRoot()
        );

        static::assertSame(
            $originalViolation->getInvalidValue(),
            $newViolation->getInvalidValue()
        );

        static::assertSame(
            $originalViolation->getPlural(),
            $newViolation->getPlural()
        );

        static::assertSame(
            $originalViolation->getCode(),
            $newViolation->getCode()
        );

        static::assertSame(
            $newPropertyPath,
            $newViolation->getPropertyPath()
        );
    }
}
