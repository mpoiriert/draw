<?php

declare(strict_types=1);

namespace SonataIntegrationBundle\Messenger\Action;

use App\Entity\MessengerMessage;
use App\Message\FailedMessage;
use App\Tests\SonataIntegrationBundle\WebTestCaseTrait;
use Draw\Bundle\TesterBundle\Messenger\MessengerTesterTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class RetryFailedMessageActionTest extends WebTestCase implements AutowiredInterface
{
    use MessengerTesterTrait;
    use WebTestCaseTrait;

    #[AutowireService]
    private MessageBusInterface $messageBus;

    public function testRetry(): void
    {
        $this->messageBus->dispatch(
            new FailedMessage(),
            [
                new TransportNamesStamp('failed'),
            ]
        );

        $this->login('admin@example.com');

        static::assertInstanceOf(
            MessengerMessage::class,
            $failedMessage = $this->entityManager
                ->getRepository(MessengerMessage::class)
                ->findOneBy(['queueName' => 'failed'])
        );
        static::assertSame(FailedMessage::class, $failedMessage->getMessageClass());

        static::$client->request('GET', sprintf('/admin/app/messengermessage/%s/retry', $failedMessage->getId()));

        static::getTransportTester('async')
            ->assertMessageMatch(RetryFailedMessageMessage::class);

        static::assertResponseStatusCodeSame(302);

        static::$client->followRedirect();

        static::assertSelectorTextContains('.alert-success', 'Retry message successfully dispatched.');
    }
}
