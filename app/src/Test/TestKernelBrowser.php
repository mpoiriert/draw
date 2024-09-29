<?php

namespace App\Test;

use Draw\Bundle\TesterBundle\HttpKernel\JWTLoginTrait;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[Exclude]
class TestKernelBrowser extends KernelBrowser
{
    use JWTLoginTrait;

    public function loginUserInAdmin(object $user): static
    {
        return $this->loginUser($user, 'user');
    }
}
