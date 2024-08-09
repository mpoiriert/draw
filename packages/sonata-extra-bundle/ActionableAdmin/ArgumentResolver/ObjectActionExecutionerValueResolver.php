<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\ArgumentResolver;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ObjectActionExecutionerValueResolver implements ValueResolverInterface
{
    public function __construct(
        private AdminFetcherInterface $adminFetcher,
        private ObjectActionExecutioner $objectActionExecutioner
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {

        $type = $argument->getType();

        if (null === $type) {
            return [];
        }

        if (ObjectActionExecutioner::class !== $type && !is_subclass_of($type, ObjectActionExecutioner::class)) {
            return [];
        }

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException) {
            return [];
        }

        if ($admin->hasSubject()) {
            $action = $request->attributes->get('_actionableAdmin')['action'];

            if (null === $action) {
                return [];
            }

            return [$this->objectActionExecutioner->initialize(
                target: $admin->getSubject(),
                admin: $admin,
                action: $action
            )];
        }

        $action = $request->request->get('action');

        if (null === $action) {
            return [];
        }

        foreach ($request->attributes as $attribute) {
            if ($attribute instanceof ProxyQuery) {
                return [$this->objectActionExecutioner->initialize(
                    target: $attribute,
                    admin: $admin,
                    action: $action,
                )];
            }
        }

        return [];
    }
}
