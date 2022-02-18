<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountLockedException extends AuthenticationException
{
    private $messageKey;

    public function __construct(string $messageKey)
    {
        $this->messageKey = $messageKey;
        parent::__construct();
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function __serialize(): array
    {
        return [$this->messageKey, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->messageKey, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
