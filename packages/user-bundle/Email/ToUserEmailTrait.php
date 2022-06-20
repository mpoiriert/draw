<?php

namespace Draw\Bundle\UserBundle\Email;

trait ToUserEmailTrait
{
    /**
     * @var mixed
     */
    private $userId = null;

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
