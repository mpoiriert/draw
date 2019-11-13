<?php namespace Draw\Bundle\UserBundle\MessageHandler;

use Draw\Bundle\UserBundle\Message\AutoConnectInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AutoConnectMessageHandler implements MessageHandlerInterface
{
    public function __invoke(AutoConnectInterface $autoLogin)
    {
        //Nothing to do since it should be done by the firewall
    }
}