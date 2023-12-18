<?php

namespace Draw\Component\Mailer\Tests\EmailWriter;

use Draw\Component\Mailer\EmailWriter\AddTemplateHeaderEmailWriter;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[CoversClass(AddTemplateHeaderEmailWriter::class)]
class AddTemplateHeaderEmailWriterTest extends TestCase
{
    private AddTemplateHeaderEmailWriter $object;

    protected function setUp(): void
    {
        $this->object = new AddTemplateHeaderEmailWriter();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EmailWriterInterface::class,
            $this->object
        );
    }

    public function testGetForEmails(): void
    {
        static::assertSame(
            ['addHeader' => -255],
            $this->object::getForEmails()
        );
    }

    public function testAddHeader(): void
    {
        $message = new TemplatedEmail();

        $message->htmlTemplate('html-template');
        $message->textTemplate('text-template');

        $this->object->addHeader($message);

        static::assertSame(
            'html-template',
            $message->getHeaders()->get('X-DrawEmail-HtmlTemplate')->getBodyAsString()
        );

        static::assertSame(
            'text-template',
            $message->getHeaders()->get('X-DrawEmail-TextTemplate')->getBodyAsString()
        );
    }
}
