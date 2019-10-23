<?php namespace Draw\Bundle\MessengerBundle\Message;

interface RedirectMessageInterface
{
    public function getUrlToRedirectTo(): ?string;
}