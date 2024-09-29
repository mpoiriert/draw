<?php

namespace Draw\Component\Mailer\Tests\Command;

use Draw\Component\Mailer\Command\SendTestEmailCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @internal
 */
#[CoversClass(SendTestEmailCommand::class)]
class SendTestEmailCommandTest extends TestCase
{
    use CommandTestTrait;

    private MailerInterface&MockObject $mailer;

    protected function setUp(): void
    {
        $this->command = new SendTestEmailCommand(
            $this->mailer = $this->createMock(MailerInterface::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:mailer:send-test-email';
    }

    public static function provideTestArgument(): iterable
    {
        yield [
            'to',
            InputArgument::REQUIRED,
        ];
    }

    public static function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $to = uniqid('email-').'@example.com';
        $this->mailer
            ->expects(static::once())
            ->method('send')
            ->with(
                static::callback(
                    function (Email $email) use ($to) {
                        $this->assertSame('Test', $email->getSubject());
                        $this->assertSame('This email as been sent as part of a test.', $email->getTextBody());
                        $this->assertSame($to, $email->getTo()[0]->getAddress());

                        return true;
                    }
                )
            )
        ;

        $this->execute(['to' => $to])
            ->test(CommandDataTester::create())
        ;
    }
}
