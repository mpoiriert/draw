<?php

namespace Draw\Bundle\SonataExtraBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CanSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(
        private SecurityHandlerInterface $decoratedSecurityHandler,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function isGranted(AdminInterface $admin, $attributes, ?object $object = null): bool
    {
        if (!$this->decoratedSecurityHandler->isGranted($admin, $attributes, $object)) {
            return false;
        }

        if (!$object) {
            return true;
        }

        foreach ((array) $attributes as $attribute) {
            if (\is_string($attribute) && !str_starts_with($attribute, 'ROLE_')) {
                if (!$this->authorizationChecker->isGranted('SONATA_CAN_'.$attribute, $object)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getBaseRole(AdminInterface $admin): string
    {
        return $this->decoratedSecurityHandler->getBaseRole($admin);
    }

    public function buildSecurityInformation(AdminInterface $admin): array
    {
        return $this->decoratedSecurityHandler->buildSecurityInformation($admin);
    }

    public function createObjectSecurity(AdminInterface $admin, object $object): void
    {
        $this->decoratedSecurityHandler->createObjectSecurity($admin, $object);
    }

    public function deleteObjectSecurity(AdminInterface $admin, object $object): void
    {
        $this->decoratedSecurityHandler->deleteObjectSecurity($admin, $object);
    }
}
