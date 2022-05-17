<?php

namespace Draw\Bundle\SonataExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminParamConverter implements ParamConverterInterface
{
    private AdminFetcherInterface $adminFetcher;

    public function __construct(AdminFetcherInterface $adminFetcher)
    {
        $this->adminFetcher = $adminFetcher;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $request->attributes->set(
                $configuration->getName(),
                $this->adminFetcher->get($request)
            );

            return true;
        } catch (AdminCodeNotFoundException $exception) {
            return false;
        }
    }

    public function supports(ParamConverter $configuration): bool
    {
        return AdminInterface::class === $configuration->getClass();
    }
}
