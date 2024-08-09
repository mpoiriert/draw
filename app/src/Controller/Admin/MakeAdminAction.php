<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\ExecutionErrorEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Response;

class MakeAdminAction
{
    public function __invoke(
        ObjectActionExecutioner $objectActionExecutioner
    ): Response {
        return $objectActionExecutioner
            ->execute(
                [
                    'execution' => function (User $user) use ($objectActionExecutioner): void {
                        $currentRoles = $user->getRoles();

                        if (\in_array('ROLE_ADMIN', $currentRoles)) {
                            $objectActionExecutioner->skip('already-admin');

                            return;
                        }

                        $user->setRoles([
                            ...$currentRoles,
                            'ROLE_ADMIN',
                        ]);

                        $objectActionExecutioner->getAdmin()->update($user);
                    },
                    'onExecutionError' => function (ExecutionErrorEvent $event): void {
                        $event->setStopExecution(false);
                    },
                ]
            );
    }
}
