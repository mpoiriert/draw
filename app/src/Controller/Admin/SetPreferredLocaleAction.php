<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class SetPreferredLocaleAction
{
    public function __invoke(
        Request $request,
        ObjectActionExecutioner $objectActionExecutioner,
    ): Response {
        return $objectActionExecutioner->execute(
            static function (User $user) use ($request, $objectActionExecutioner): void {
                $user->setPreferredLocale($request->get('_locale') ?? 'en');

                $objectActionExecutioner->getAdmin()->update($user);
            }
        );
    }
}
