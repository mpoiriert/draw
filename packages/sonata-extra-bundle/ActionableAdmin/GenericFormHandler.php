<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class GenericFormHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $twig,
        private AdminActionLoader $adminActionLoader,
        private ?CsrfTokenManagerInterface $csrfTokenManager
    ) {
    }

    public function execute(
        ObjectActionExecutioner $objectActionExecutioner,
        Request $request,
        string $formClass,
        mixed $data,
        callable|array $executions
    ): ?Response {
        $executions = \is_callable($executions) ? ['execution' => $executions] : $executions;

        $previousExecution = $executions['execution'];

        $executions['execution'] = function (object $object) use ($objectActionExecutioner, $previousExecution): void {
            $previousExecution($object, $objectActionExecutioner->options['form.data']);
        };

        $form = $this->getForm($objectActionExecutioner, $formClass, $data, $request);

        $mayBeSubmitted = !$objectActionExecutioner->isBatch() || $request->request->has('fromGenericFormHandler');

        $duplicatedRequest = $this->cleanRequest($request);

        if ($mayBeSubmitted) {
            $form->handleRequest($duplicatedRequest);
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            $action = $objectActionExecutioner->getAction();
            $admin = $objectActionExecutioner->getAdmin();

            return new Response(
                $this->twig->render(
                    '@DrawSonataExtra/Action/generic_form.html.twig',
                    [
                        'admin' => $admin,
                        'base_template' => $admin->getTemplateRegistry()->getTemplate('layout'),
                        'form' => $form->createView(),
                        'action' => $action,
                        'adminAction' => $this->adminActionLoader->getActions($admin)[$action],
                    ]
                )
            );
        }

        $objectActionExecutioner->options['form.data'] = $objectActionExecutioner->isBatch()
            ? $form->get('form')->getData()
            : $form->getData();

        return $objectActionExecutioner
            ->execute($executions);
    }

    /**
     * This is to remove parameters from sonata batch action.
     */
    private function cleanRequest(Request $request): Request
    {
        $duplicatedRequest = $request->duplicate();
        foreach (['_sonata_csrf_token', 'filter', 'idx', 'action', 'all_elements'] as $key) {
            $duplicatedRequest->request->remove($key);
        }

        return $duplicatedRequest;
    }

    private function getForm(
        ObjectActionExecutioner $objectActionExecutioner,
        string $formClass,
        mixed $data,
        Request $request,
    ): FormInterface {
        $action = $objectActionExecutioner->getAction();
        $admin = $objectActionExecutioner->getAdmin();

        if (!$objectActionExecutioner->isBatch()) {
            return $this->formFactory
                ->createBuilder($formClass, $data)
                ->setAction($admin->generateObjectUrl($action, $objectActionExecutioner->getSubject()))
                ->getForm();
        }

        $formBuilder = $this->formFactory
            ->createNamedBuilder('', data: ['form' => $data])
            ->add('fromGenericFormHandler', HiddenType::class, ['data' => true])
            ->add(
                'form',
                $formClass,
                ['label' => false]
            )
            ->setAction($admin->generateUrl('batch', ['filter' => $admin->getFilterParameters()]))
            ->add(
                'data',
                HiddenType::class,
                [
                    'data' => json_encode([
                        'idx' => $request->get('idx'),
                        'all_elements' => $request->get('all_elements'),
                        'action' => $action,
                    ]),
                ]
            )
            ->add(
                'confirmation',
                HiddenType::class,
                [
                    'data' => 'ok',
                ]
            );

        if ($this->csrfTokenManager) {
            $formBuilder->add(
                '_sonata_csrf_token',
                HiddenType::class,
                [
                    'data' => $this->csrfTokenManager->getToken('sonata.batch')->getValue(),
                ]
            );
        }

        return $formBuilder->getForm();
    }
}
