<?php

namespace App\Tests\Messenger\DoctrineEnvelopeEntityReference\EventListener;

use App\Document\TestDocument;
use App\Entity\User;
use App\Message\NewTestDocumentMessage;
use App\Message\NewUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Exception\ObjectNotFoundException;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class PropertyReferenceEncodingListenerTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireService]
    private EnvelopeFinderInterface $envelopeFinder;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    #[AutowireService]
    private EventDispatcherInterface $eventDispatcher;

    private bool $preEncodeEventCalled = false;

    private bool $postEncodeEventCalled = false;

    private static User $user;

    public function testSend(): void
    {
        self::$user = (new User())
            ->setEmail(uniqid().'@example.com')
        ;

        $this->eventDispatcher->addListener(
            PreEncodeEvent::class,
            function (PreEncodeEvent $event): void {
                $message = $event->getEnvelope()->getMessage();
                if (!$message instanceof NewUserMessage) {
                    return;
                }

                $this->preEncodeEventCalled = true;

                static::assertNull(
                    ReflectionAccessor::getPropertyValue(
                        $message,
                        'user'
                    ),
                    'User property should be null at this point.'
                );
            },
            -1
        );

        $this->eventDispatcher->addListener(
            PostEncodeEvent::class,
            function (PostEncodeEvent $event): void {
                $message = $event->getEnvelope()->getMessage();
                if (!$message instanceof NewUserMessage) {
                    return;
                }

                $this->postEncodeEventCalled = true;

                static::assertSame(
                    self::$user,
                    ReflectionAccessor::getPropertyValue(
                        $message,
                        'user'
                    ),
                    'User property should be restored at this point.'
                );
            },
            -1
        );

        $this->entityManager->persist(self::$user);

        $this->entityManager->flush();

        static::assertTrue($this->preEncodeEventCalled);

        static::assertTrue($this->postEncodeEventCalled);
    }

    #[Depends('testSend')]
    public function testLoad(): void
    {
        $envelope = $this->envelopeFinder->findByTags([self::$user->getEmail()])[0];

        $message = $envelope->getMessage();

        static::assertInstanceOf(NewUserMessage::class, $message);

        static::assertSame(
            self::$user->getEmail(),
            $message->getUser()->getEmail()
        );
    }

    #[Depends('testSend')]
    public function testLoadNotFound(): void
    {
        $this->entityManager
            ->getConnection()
            ->delete('draw_acme__user', ['email' => self::$user->getEmail()])
        ;

        $envelope = $this->envelopeFinder->findByTags([self::$user->getEmail()])[0];

        $message = $envelope->getMessage();

        static::assertInstanceOf(NewUserMessage::class, $message);

        $this->expectException(ObjectNotFoundException::class);

        $this->expectExceptionMessage(
            \sprintf(
                'Object of class [%s] not found. Identifiers [%s]',
                User::class,
                json_encode(['id' => self::$user->getId()]),
            )
        );

        $message->getUser();
    }

    public function testODM(): void
    {
        $testDocument = new TestDocument();

        $manager = static::getContainer()->get('doctrine_mongodb')->getManager();

        $manager->persist($testDocument);

        $manager->flush();

        $envelope = $this->envelopeFinder->findByTags([$testDocument->id])[0];

        $message = $envelope->getMessage();

        static::assertInstanceOf(NewTestDocumentMessage::class, $message);

        static::assertSame(
            $testDocument,
            $message->getTestDocument()
        );
    }
}
