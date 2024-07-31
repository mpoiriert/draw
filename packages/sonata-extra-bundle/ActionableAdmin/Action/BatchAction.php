<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Action;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ActionableInterface;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\BatchActionInterface;
use Psr\Container\ContainerInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsController]
class BatchAction implements ServiceSubscriberInterface
{
    public static function getSubscribedServices(): array
    {
        return [
            'controller_resolver' => 'controller_resolver',
        ];
    }

    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function __invoke(
        ProxyQuery $query,
        ActionableInterface $admin,
        Request $request,
    ): RedirectResponse {
        $query->select('o.id as id');

        $action = $this->loadAction($admin, $request->request->get('action'));

        $controller = $this->container->get('controller_resolver')->getController(
            new Request([], [], ['_controller' => $action->getController()])
        );

        if (!$controller instanceof BatchActionInterface) {
            throw new \RuntimeException(sprintf('Controller "%s" must implement BatchActionInterface', $action->getController()));
        }

        foreach ($query->execute() as $id) {
            $object = $admin->getObject($id['id']);

            \call_user_func($controller->getBatchCallable(), $admin, $object);
        }

        return new RedirectResponse($admin->generateUrl('list'));
    }

    private function loadAction(ActionableInterface $admin, string $actionName): AdminAction
    {
        foreach ($admin->getActions() as $action) {
            if ($action->getName() === $actionName) {
                return $action;
            }
        }

        throw new \RuntimeException(sprintf('Action "%s" not found', $actionName));
    }
}
