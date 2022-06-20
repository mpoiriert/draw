<?php

namespace Draw\Bundle\UserBundle\Email;

interface ToUserEmailInterface
{
    /**
     * @return mixed
     */
    public function getUserId();
}
