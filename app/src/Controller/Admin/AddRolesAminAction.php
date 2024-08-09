<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AddRolesForm;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\GenericFormHandler;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddRolesAminAction
{
    public function __invoke(
        ObjectActionExecutioner $objectActionExecutioner,
        Request $request,
        GenericFormHandler $genericFormHandler
    ): Response {
        return $genericFormHandler
            ->execute(
                $objectActionExecutioner,
                $request,
                AddRolesForm::class,
                null,
                function (User $user, array $data) use ($objectActionExecutioner): void {
                    $roles = $data['roles'];

                    $missingRoles = array_diff($roles, $user->getRoles());

                    if (\count($missingRoles) > 0) {
                        $user->setRoles(array_merge($user->getRoles(), $missingRoles));
                        $objectActionExecutioner->getAdmin()->update($user);
                    } else {
                        $objectActionExecutioner->skip('all-roles-already-set');
                    }
                }
            );
    }
}
