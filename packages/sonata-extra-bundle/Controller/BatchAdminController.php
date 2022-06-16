<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BatchAdminController extends AbstractAdminController
{
    /**
     * @throws NotFoundHttpException If the HTTP method is not POST
     * @throws \RuntimeException     If the batch action is not defined
     */
    public function batchAction(Request $request): Response
    {
        if (Request::METHOD_POST !== $restMethod = $request->getMethod()) {
            throw new NotFoundHttpException(sprintf('Invalid request method given "%s", %s expected', $restMethod, Request::METHOD_POST));
        }

        $this->validateCsrfToken($request, 'sonata.batch');

        $forwardedRequest = $this->createForwardRequest($request);

        $action = $forwardedRequest->request->get('action');

        if (!\is_string($action)) {
            throw new \RuntimeException('The action is not defined');
        }

        try {
            $actionExecutable = $this->getBatchActionExecutable($action);
        } catch (\Throwable $error) {
            $message = sprintf(
                'You must define a valid `controller` configuration for your batch action `%s`.',
                $action,
            );
            throw new \RuntimeException($message, 0, $error);
        }

        $idx = $forwardedRequest->request->all('idx');
        $allElements = $forwardedRequest->request->getBoolean('all_elements');

        if (0 === \count($idx) && !$allElements) {
            $this->addFlash(
                'sonata_flash_info',
                $this->trans('flash_batch_empty', [], 'SonataAdminBundle')
            );

            return $this->redirectToList();
        }

        if ($response = $this->generateAskConfigurationResponse($forwardedRequest)) {
            return $response;
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->buildPager();
        $query = $datagrid->getQuery();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        $this->admin->preBatchAction($action, $query, $idx, $allElements);
        foreach ($this->admin->getExtensions() as $extension) {
            if (method_exists($extension, 'preBatchAction')) {
                $extension->preBatchAction($this->admin, $action, $query, $idx, $allElements);
            }
        }

        if (0 === \count($idx) && !$allElements) {
            $this->addFlash(
                'sonata_flash_info',
                $this->trans('flash_batch_no_elements_processed', [], 'SonataAdminBundle')
            );

            return $this->redirectToList();
        }

        if (\count($idx) > 0) {
            $this->admin->getModelManager()->addIdentifiersToQuery($this->admin->getClass(), $query, $idx);
        }

        return \call_user_func($actionExecutable, $query, $forwardedRequest);
    }

    private function createForwardRequest(Request $request): Request
    {
        $encodedData = $request->get('data');

        $forwardedRequest = $request->duplicate();

        if (null === $encodedData) {
            return $forwardedRequest;
        }

        if (!\is_string($encodedData)) {
            throw new BadRequestParamHttpException('data', 'string', $encodedData);
        }

        try {
            $forwardedRequest->request->add(json_decode($encodedData, true, 512, \JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
            throw new BadRequestHttpException('Unable to decode batch data');
        }

        return $forwardedRequest;
    }

    private function generateAskConfigurationResponse(
        Request $request
    ): ?Response {
        $batchAction = $this->admin->getBatchActions()[$request->request->get('action')];
        $datagrid = $this->admin->getDatagrid();

        switch (true) {
            case true !== ($batchAction['ask_confirmation'] ?? true):
            case 'ok' === $request->get('confirmation', false):
                return null;
            default:
                $data = $request->request->all();
                $data['all_elements'] = $request->request->getBoolean('all_elements');
                unset($data['_sonata_csrf_token']);
                $formView = $datagrid->getForm()->createView();
                $this->setFormTheme($formView, $this->admin->getFilterTheme());

                return $this->renderWithExtraParams(
                    $batchAction['template'] ?? $this->admin->getTemplateRegistry()->getTemplate('batch_confirmation'),
                    [
                        'action' => 'list',
                        'action_label' => $batchAction['label'],
                        'batch_translation_domain' => $batchAction['translation_domain'] ?? $this->admin->getTranslationDomain(),
                        'datagrid' => $datagrid,
                        'form' => $formView,
                        'data' => $data,
                        'csrf_token' => $this->getCsrfToken('sonata.batch'),
                    ]
                );
        }
    }

    private function getBatchActionExecutable(string $action): callable
    {
        $batchActions = $this->admin->getBatchActions();
        if (!\array_key_exists($action, $batchActions)) {
            throw new \RuntimeException(sprintf('The `%s` batch action is not defined', $action));
        }

        $controller = $batchActions[$action]['controller'];

        // This is to trigger an exception if the controller is not found
        $this->container
            ->get('controller_resolver')
            ->getController(new Request([], [], ['_controller' => $controller]));

        return function (ProxyQueryInterface $query, Request $request) use ($controller) {
            $request->attributes->set('_controller', $controller);
            $request->attributes->set('query', $query);

            return $this->container->get('http_kernel')->handle($request, HttpKernelInterface::SUB_REQUEST);
        };
    }
}
