<?php

namespace Draw\Component\Mailer\Tests\EmailWriter;

use Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[CoversClass(DefaultFromEmailWriter::class)]
class DefaultFromEmailWriterTest extends TestCase
{
    private DefaultFromEmailWriter $object;

    private Address $address;

    protected function setUp(): void
    {
        $this->object = new DefaultFromEmailWriter(
            $this->address = new Address(uniqid('test@').'.com')
        );
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
            ['setDefaultFrom' => -255],
            $this->object::getForEmails()
        );
    }

    public function testSetDefaultFrom(): void
    {
        $this->object->setDefaultFrom($email = new Email());

        static::assertSame(
            [$this->address],
            $email->getFrom()
        );
    }

    public function testSetDefaultFromDoesNotOverride(): void
    {
        $email = new Email();
        $email->from($value = new Address('email@example.com'));

        $this->object->setDefaultFrom($email);

        static::assertSame(
            [$value],
            $email->getFrom()
        );
    }
}
