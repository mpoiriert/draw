<?php

namespace Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenProcessor
{
    public function __construct(protected TokenStorageInterface $tokenStorage, private string $key = 'token')
    {
    }

    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }

    public function __invoke(array $record): array
    {
        $data = null;

        if ($token = $this->getToken()) {
            $data = [
                'authenticated' => (bool) ($user = $token->getUser()),
                'roles' => $token->getRoleNames(),
                'user_identifier' => $token->getUserIdentifier(),
            ];

            if ($user instanceof SecurityUserInterface) {
                $data['user_id'] = (string) $user->getId();
            }
        }

        $record['extra'][$this->key] = $data;

        return $record;
    }
}
