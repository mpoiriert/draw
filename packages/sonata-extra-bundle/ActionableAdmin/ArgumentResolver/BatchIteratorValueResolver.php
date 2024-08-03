<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\ArgumentResolver;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\BatchIterator;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class BatchIteratorValueResolver implements ValueResolverInterface
{
    public function __construct(
        private AdminFetcherInterface $adminFetcher,
        private BatchIterator $batchIterator
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (null === $type) {
            return [];
        }

        if (BatchIterator::class !== $type && !is_subclass_of($type, BatchIterator::class)) {
            return [];
        }

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException) {
            return [];
        }

        $action = $request->request->get('action');

        if (null === $action) {
            return [];
        }

        foreach ($request->attributes as $attribute) {
            if ($attribute instanceof ProxyQuery) {
                return [$this->batchIterator->initialize(
                    query: $attribute,
                    admin: $admin,
                    action: $action,
                )];
            }
        }

        return [];
    }
}
