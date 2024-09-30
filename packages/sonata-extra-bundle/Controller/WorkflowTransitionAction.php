<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Registry;

#[AsController]
class WorkflowTransitionAction
{
    public function __invoke(
        Registry $workflowRegistry,
        ObjectActionExecutioner $objectActionExecutioner,
        Request $request,
        AdminInterface $admin,
    ): ?Response {
        return $objectActionExecutioner
            ->execute(
                [
                    'execution' => static function (object $object) use ($workflowRegistry, $request, $admin): void {
                        $workflow = $workflowRegistry->get($object);

                        $transition = $request->get('transition');
                        if (null === $transition) {
                            throw new BadRequestHttpException('missing transition to apply');
                        }

                        if (!$workflow->can($object, $transition)) {
                            throw new BadRequestHttpException(\sprintf('transition %s could not be applied to object %s', $transition, $admin->toString($object)));
                        }

                        $marking = $workflow->apply($object, $transition);

                        $admin->update($object);
                    },
                ]
            )
        ;
    }
}
