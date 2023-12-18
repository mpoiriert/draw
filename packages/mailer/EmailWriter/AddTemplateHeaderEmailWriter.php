<?php

namespace Draw\Component\Mailer\EmailWriter;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Header\UnstructuredHeader;

class AddTemplateHeaderEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return [
            'addHeader' => -255,
        ];
    }

    public function addHeader(TemplatedEmail $message): void
    {
        $headers = $message->getHeaders();
        if ($template = $message->getHtmlTemplate()) {
            $headers->add(new UnstructuredHeader('X-DrawEmail-HtmlTemplate', $template));
        }
        if ($template = $message->getTextTemplate()) {
            $headers->add(new UnstructuredHeader('X-DrawEmail-TextTemplate', $template));
        }
    }
}
