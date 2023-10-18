<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidationListener implements EventSubscriberInterface
{
    private const PREFIXES_DEFAULT = [
        'query' => '$.query',
        'body' => '$.body',
    ];

    private array $prefixes;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelController', -5],
        ];
    }

    public function __construct(private ValidatorInterface $validator, array $prefixes = [])
    {
        $this->prefixes = array_merge(self::PREFIXES_DEFAULT, $prefixes);
    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();
        $constraints = [];
        if ($request->attributes->has('_draw_body_validation')) {
            $configuration = $request->attributes->get('_draw_body_validation');
            \assert($configuration instanceof RequestBody);

            $parameterName = $configuration->argumentName;

            $constraints = array_merge(
                $constraints,
                $this->bonifyConstraints(
                    $this->validate(
                        $request->attributes->get($parameterName),
                        $configuration
                    ),
                    $this->prefixes['body']
                )
            );
        }

        foreach ($request->attributes->get('_draw_query_parameters_validation', []) as $parameter) {
            \assert($parameter instanceof QueryParameter);

            $constraints = array_merge(
                $constraints,
                $this->bonifyConstraints(
                    $this->validator->validate(
                        $request->attributes->get($parameter->name),
                        $parameter->constraints
                    ),
                    $this->prefixes['query'] ? $this->prefixes['query'].'.'.$parameter->name : $parameter->name
                )
            );
        }

        if (\count($constraints)) {
            throw new ConstraintViolationListException(new ConstraintViolationList($constraints));
        }
    }

    /**
     * @return ConstraintViolation[]
     */
    private function bonifyConstraints(ConstraintViolationListInterface $violations, string $pathPrefix): array
    {
        $constraintViolations = [];
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if ($pathPrefix) {
                $path = $pathPrefix.(str_starts_with($path, '[') || !$path ? $path : '.'.$path);
            }
            /* @var ConstraintViolationInterface $violation */
            $constraintViolations[] = new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $path,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode()
            );
        }

        return $constraintViolations;
    }

    private function validate($object, RequestBody $requestBody): ConstraintViolationListInterface
    {
        if ($requestBody->validate) {
            return $this->validator->validate($object, null, $requestBody->validationGroups ?? ['Default']);
        }

        return new ConstraintViolationList();
    }
}
