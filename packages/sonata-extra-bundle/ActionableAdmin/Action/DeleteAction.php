<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Action;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\EventListener\CsrfTokenValidatorListener;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Twig\Environment;

#[AsController]
class DeleteAction
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function __invoke(
        ObjectActionExecutioner $objectActionExecutioner,
        Request $request,
    ): ?Response {
        $objectActionExecutioner->options[CsrfTokenValidatorListener::INTENTION] = 'sonata.delete';

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_DELETE], true)) {
            $objectActionExecutioner->options[CsrfTokenValidatorListener::TOKEN] = $request->get('_sonata_csrf_token');
        }

        return $objectActionExecutioner
            ->execute(
                [
                    'preExecution' => function () use ($objectActionExecutioner, $request): ?Response {
                        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_DELETE], true)) {
                            return null;
                        }

                        $admin = $objectActionExecutioner->getAdmin();
                        $templateRegistry = $admin->getTemplateRegistry();

                        return new Response(
                            $this->twig->render(
                                $templateRegistry->getTemplate('delete'),
                                [
                                    'base_template' => $templateRegistry->getTemplate('layout'),
                                    'action' => $objectActionExecutioner->getAction(),
                                    'object' => $objectActionExecutioner->getSubject(),
                                    'admin' => $admin,
                                    'csrf_token' => $objectActionExecutioner->options[CsrfTokenValidatorListener::TOKEN] ?? null,
                                ]
                            )
                        );
                    },
                    'execution' => static function ($object) use ($objectActionExecutioner): void {
                        $objectActionExecutioner->getAdmin()->delete($object);
                    },
                ]
            )
        ;
    }
}
