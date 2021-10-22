<?php

namespace Draw\Bundle\OpenApiBundle\Request\Listener;

use Draw\Bundle\OpenApiBundle\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationSubscriber implements EventSubscriberInterface
{
    private const PREFIXES_DEFAULT = [
        'query' => '$.query',
        'body' => '$.body',
    ];

    /**
     * @var ValidatorInterface
     */
    private $validator;

    private $prefixes = [];

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -5],
        ];
    }

    public function __construct(ValidatorInterface $validator, array $prefixes = [])
    {
        $this->validator = $validator;
        $this->prefixes = array_merge(static::PREFIXES_DEFAULT, $prefixes);
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $constraints = [];
        if ($request->attributes->has('_draw_body_validation')) {
            /** @var ParamConverter $configuration */
            $configuration = $request->attributes->get('_draw_body_validation');
            $constraints += $this->bonifyConstraints($this->validate(
                $request->attributes->get($configuration->getName()),
                $configuration
            ),
                $this->prefixes['body']
            );
        }

        foreach ($request->attributes->get('_draw_query_parameters_validation', []) as $parameter) {
            /* @var QueryParameter $parameter */
            $constraints += $this->bonifyConstraints(
                $this->validator->validate(
                    $request->attributes->get($parameter->name),
                    $parameter->constraints
                ),
                $this->prefixes['query'] ? $this->prefixes['query'].'.'.$parameter->name : $parameter->name
            );
        }

        if (count($constraints)) {
            $exception = new ConstraintViolationListException();
            $exception->setViolationList(new ConstraintViolationList($constraints));
            throw $exception;
        }
    }

    private function bonifyConstraints(ConstraintViolationListInterface $violations, string $pathPrefix)
    {
        $constraintViolations = [];
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if ($pathPrefix) {
                $path = $pathPrefix.(0 === strpos($path, '[') || !$path ? $path : '.'.$path);
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

    private function validate($object, ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if ($options['validate'] ?? true) {
            $validatorOptions = $paramConverter->getOptions()['validator'] ?? [];
            $groups = $validatorOptions['groups'] ?? ['Default'];

            return $this->validator->validate($object, null, $groups);
        }

        return null;
    }
}
