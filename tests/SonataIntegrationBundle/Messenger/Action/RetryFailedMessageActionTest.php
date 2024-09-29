<?php

declare(strict_types=1);

namespace App\Tests\SonataIntegrationBundle\Messenger\Action;

use App\Entity\MessengerMessage;
use App\Entity\User;
use App\Message\FailedMessage;
use App\Test\PHPUnit\Extension\SetUpAutowire\AutowireAdminUser;
use App\Test\TestKernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\Messenger\MessengerTesterTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

/**
 * @internal
 */
class RetryFailedMessageActionTest extends WebTestCase implements AutowiredInterface
{
    use MessengerTesterTrait;

    #[AutowireClient]
    private TestKernelBrowser $client;

    #[AutowireService]
    private MessageBusInterface $messageBus;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    #[AutowireAdminUser]
    private User $admin;

    public function testRetry(): void
    {
        $this->messageBus->dispatch(
            new FailedMessage(),
            [
                new TransportNamesStamp('failed'),
            ]
        );

        $this->client->loginUserInAdmin($this->admin);

        static::assertInstanceOf(
            MessengerMessage::class,
            $failedMessage = $this->entityManager
                ->getRepository(MessengerMessage::class)
                ->findOneBy(['queueName' => 'failed'])
        );
        static::assertSame(FailedMessage::class, $failedMessage->getMessageClass());

        $this->client->request(
            'GET',
            \sprintf('/admin/app/messengermessage/%s/retry', $failedMessage->getId())
        );

        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        static::getTransportTester('async')
            ->assertMessageMatch(RetryFailedMessageMessage::class)
        ;

        $this->client->followRedirect();

        static::assertSelectorTextContains('.alert-success', 'Retry message successfully dispatched.');
    }
}
