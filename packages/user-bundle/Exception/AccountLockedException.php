<?php

namespace Draw\Bundle\UserBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountLockedException extends AuthenticationException
{
    private string $messageKey;

    /**
     * @var array|string[]
     */
    private array $reasons = [];

    /**
     * @param array|string[] $reasons
     */
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

    /**
     * @return array|string[]
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    /**
     * @phpstan-return mixed[]
     */
    public function __serialize(): array
    {
        return [$this->messageKey, $this->reasons, parent::__serialize()];
    }

    /**
     * @phpstan-param mixed[] $data
     */
    public function __unserialize(array $data): void
    {
        [$this->messageKey, $this->reasons, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
