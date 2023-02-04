<?php

namespace Draw\Bundle\UserBundle\Email;

trait ToUserEmailTrait
{
    private string|int|null $userId = null;

    public function setUserId(string|int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): string|int|null
    {
        return $this->userId;
    }
}
