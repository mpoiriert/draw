<?php

namespace Draw\Bundle\MessengerBundle\Message;

use Symfony\Component\HttpFoundation\Response;

interface ResponseGeneratorMessageInterface
{
    public function generateResponse(): Response;
}
