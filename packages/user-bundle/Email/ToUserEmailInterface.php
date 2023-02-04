<?php

namespace Draw\Bundle\UserBundle\Email;

interface ToUserEmailInterface
{
    public function getUserId(): string|int|null;
}
