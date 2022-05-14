<?php

namespace Draw\Component\Mailer\Tests\Command;

use Draw\Component\Mailer\Command\SendTestEmailCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @covers \Draw\Component\Mailer\Command\SendTestEmailCommand
 */
class SendTestEmailCommandTest extends TestCase
{
    use CommandTestTrait;

    /**
     * @var MailerInterface|MockObject
     */
    private MailerInterface $mailer;

    public function createCommand(): Command
    {
        return new SendTestEmailCommand(
            $this->mailer = $this->createMock(MailerInterface::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:mailer:send-test-email';
    }

    public function getCommandDescription(): string
    {
        return 'Send a test email.';
    }

    public function provideTestArgument(): iterable
    {
        yield [
            'to',
            InputArgument::REQUIRED,
            'Email to send to',
            null,
        ];
    }

    public function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $to = uniqid('email-').'@example.com';
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->callback(
                    function (Email $email) use ($to) {
                        $this->assertSame('Test', $email->getSubject());
                        $this->assertSame('This email as been sent as part of a test.', $email->getTextBody());
                        $this->assertSame($to, $email->getTo()[0]->getAddress());

                        return true;
                    }
                )
            );

        $this->execute(['to' => $to])
            ->test(CommandDataTester::create());
    }
}
