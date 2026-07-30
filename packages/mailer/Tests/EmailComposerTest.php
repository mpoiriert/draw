<?php

namespace Draw\Component\Mailer\Tests;

use Draw\Component\Mailer\Email\LocalizeEmailInterface;
use Draw\Component\Mailer\EmailComposer;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Mailer\Tests\Stub\EmailWriter\EmailWriterStub;
use Draw\Component\Tester\DoubleTrait;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Translation\Translator;

/**
 * @internal
 */
#[CoversClass(EmailComposer::class)]
#[AllowMockObjectsWithoutExpectations]
class EmailComposerTest extends TestCase
{
    use DoubleTrait;

    private EmailComposer $object;

    private ContainerInterface&MockObject $serviceLocator;

    private Translator&MockObject $translator;

    protected function setUp(): void
    {
        $this->object = new EmailComposer(
            $this->serviceLocator = $this->createMock(ContainerInterface::class),
            $this->translator = $this->createMock(Translator::class)
        );
    }

    public function testWriterMutator(): void
    {
        $this->assertSame([], $this->object->getWriters(\stdClass::class));

        $this->object->addWriter(\stdClass::class, $writer1 = uniqid('writer-'), $method1 = uniqid('method-'));

        $this->assertSame(
            [],
            $this->object->getWriters(uniqid('other-class-'))
        );

        $this->assertSame(
            [
                [$writer1, $method1],
            ],
            $this->object->getWriters(\stdClass::class)
        );

        $this->object->addWriter(\stdClass::class, $writer2 = uniqid('writer-'), $method2 = uniqid('method-'), 1);

        $this->assertSame(
            [
                [$writer2, $method2],
                [$writer1, $method1],
            ],
            $this->object->getWriters(\stdClass::class)
        );
    }

    public function testComposeMessage(): void
    {
        $message = new TemplatedEmail();

        $envelope = new Envelope(new Address('test@example.com'), [new Address('test@example.com')]);

        $this->object->addWriter(Message::class, $writer1 = uniqid('writer-1-'), 'method1');
        $this->object->addWriter(Email::class, $writer2 = uniqid('writer-2-'), 'method2');
        $this->object->addWriter(uniqid('other-class-'), uniqid('writer-'), uniqid('method-'));

        $this->serviceLocator
            ->expects($this->exactly(2))
            ->method('get')
            ->with(
                ...static::withConsecutive(
                    [$writer2],
                    [$writer1]
                )
            )
            ->willReturn(
                $emailWriter = $this->createMock(EmailWriterStub::class)
            )
        ;

        $emailWriter
            ->expects($this->once())
            ->method('method1')
            ->with(
                $message,
                $envelope
            )
        ;

        $emailWriter
            ->expects($this->once())
            ->method('method2')
            ->with(
                $message,
                $envelope
            )
        ;

        $this->object->compose($message, $envelope);
    }

    public function testRegisterEmailWriter(): void
    {
        $message = $this->createStub(Email::class);

        $envelope = new Envelope(new Address('test@example.com'), [new Address('test@example.com')]);

        $emailWriter = new class implements EmailWriterInterface {
            public int $compose1CallCounter = 0;

            public int $compose2CallCounter = 0;

            public static function getForEmails(): array
            {
                return [
                    'compose1',
                    'compose2',
                ];
            }

            public function compose1(Email $email): void
            {
                ++$this->compose1CallCounter;
            }

            public function compose2(Message $email): void
            {
                ++$this->compose2CallCounter;
            }
        };

        $this->object->registerEmailWriter($emailWriter);

        $this->assertSame(
            [
                [$emailWriter, 'compose1'],
            ],
            $this->object->getWriters(Email::class)
        );

        $this->assertSame(
            [
                [$emailWriter, 'compose2'],
            ],
            $this->object->getWriters(Message::class)
        );

        $this->serviceLocator
            ->expects($this->never())
            ->method('get')
        ;

        $this->object->compose($message, $envelope);

        $this->assertSame(1, $emailWriter->compose1CallCounter);
        $this->assertSame(1, $emailWriter->compose2CallCounter);
    }

    public function testComposeLocalizeEmail(): void
    {
        $message = new class extends Email implements LocalizeEmailInterface {
            public function getLocale(): string
            {
                return 'fr';
            }
        };

        $this->translator
            ->expects($this->once())
            ->method('getLocale')
            ->willReturn('en')
        ;

        $this->translator
            ->expects($this->exactly(2))
            ->method('setLocale')
            ->with(
                ...static::withConsecutive(
                    ['fr'],
                    ['en']
                )
            )
        ;

        $this->object->compose(
            $message,
            new Envelope(new Address('test@example.com'), [new Address('test@example.com')])
        );
    }
}
