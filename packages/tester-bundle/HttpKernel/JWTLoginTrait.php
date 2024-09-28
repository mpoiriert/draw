<?php

namespace Draw\Bundle\TesterBundle\HttpKernel;

use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait JWTLoginTrait
{
    private JwtAuthenticator $jwtAuthenticator;

    #[Required]
    public function setJwtAuthenticator(JwtAuthenticator $jwtAuthenticator): static
    {
        $this->jwtAuthenticator = $jwtAuthenticator;

        return $this;
    }

    public function loginUser(object $user, string $firewallContext = 'main'): static
    {
        if ($user instanceof UserInterface) {
            $this->server['HTTP_AUTHORIZATION'] = 'Bearer '.$this->jwtAuthenticator->generaToken($user);
        }

        return parent::loginUser($user, $firewallContext);
    }

    public function logout(): void
    {
        unset($this->server['Authorization']);
    }
}
