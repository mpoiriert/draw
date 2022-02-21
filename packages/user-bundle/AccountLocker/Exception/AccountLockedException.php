<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountLockedException extends AuthenticationException
{
    private $messageKey;

    private $reasons;

    public function __construct(string $messageKey, array $reasons)
    {
        $this->reasons = $reasons;
        $this->messageKey = $messageKey;
        parent::__construct();
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function __serialize(): array
    {
        return [$this->messageKey, $this->reasons, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->messageKey, $this->reasons, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
