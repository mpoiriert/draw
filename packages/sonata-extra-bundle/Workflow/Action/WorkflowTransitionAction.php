<?php

namespace Draw\Bundle\SonataExtraBundle\Workflow\Action;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * @template A of AdminInterface
 * @template T of object
 */
#[AsController]
class WorkflowTransitionAction
{
    public function __invoke(
        Registry $workflowRegistry,
        ObjectActionExecutioner $objectActionExecutioner,
        Request $request,
    ): ?Response {
        return $objectActionExecutioner
            ->execute(
                [
                    'execution' => fn (object $object) => $this->executeTransition($objectActionExecutioner, $workflowRegistry->get($object), $object, $request->get('transition')),
                ]
            )
        ;
    }

    private function executeTransition(ObjectActionExecutioner $objectActionExecutioner, WorkflowInterface $workflow, object $object, ?string $transition): ?Response
    {
        if (null === $transition) {
            throw new BadRequestHttpException('missing transition to apply');
        }

        if (!$workflow->can($object, $transition)) {
            throw new BadRequestHttpException(\sprintf('transition %s could not be applied to object %s', $transition, $objectActionExecutioner->getAdmin()->toString($object)));
        }

        if ($response = $this->preApplyTransition($objectActionExecutioner, $object, $transition)) {
            return $response;
        }

        $workflow->apply($object, $transition);

        $objectActionExecutioner->getAdmin()->update($object);

        if ($response = $this->postApplyTransition($objectActionExecutioner, $object, $transition)) {
            return $response;
        }

        return null;
    }

    /**
     * @param ObjectActionExecutioner<A,T> $objectActionExecutioner
     * @param T                            $object
     */
    protected function preApplyTransition(
        ObjectActionExecutioner $objectActionExecutioner,
        object $object,
        string $transition,
    ): ?Response {
        return null;
    }

    /**
     * @param ObjectActionExecutioner<A,T> $objectActionExecutioner
     * @param T                            $object
     */
    protected function postApplyTransition(
        ObjectActionExecutioner $objectActionExecutioner,
        object $object,
        string $transition,
    ): ?Response {
        return null;
    }
}
