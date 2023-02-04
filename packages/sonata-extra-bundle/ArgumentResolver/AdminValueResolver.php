<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataExtraBundle\ArgumentResolver;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * REMOVE_WHEN_FIX https://github.com/sonata-project/SonataAdminBundle/issues/7846.
 */
final class AdminValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private AdminFetcherInterface $adminFetcher)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        if (null === $type) {
            return false;
        }

        if (AdminInterface::class !== $type) {
            return false;
        }

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return is_a($admin, $type);
    }

    /**
     * @return iterable<AdminInterface<object>>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->adminFetcher->get($request);
    }
}
