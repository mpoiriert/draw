<?php namespace Draw\Bundle\UserBundle\MessageHandler;

use Draw\Bundle\UserBundle\Message\ResetPassword;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordMessageHandler implements MessageHandlerInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(ResetPassword $resetPassword)
    {
        $resetPassword->setUrlToRedirectTo($this->urlGenerator->generate('index'));
    }
}