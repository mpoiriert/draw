<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Tests\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\DoctrineBusMessageBundle\Listener\DoctrineBusMessageEventSubscriber;
use Draw\Bundle\DoctrineBusMessageBundle\Message\AsyncMessageInterface;
use Draw\Bundle\DoctrineBusMessageBundle\Tests\fixtures\Message\TestMessage;
use Draw\Bundle\DoctrineBusMessageBundle\Tests\TestCase;
use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Test\Entity\MessageHolder;
use Test\Entity\NotMessageHolder;

/**
 * @covers \Draw\Bundle\DoctrineBusMessageBundle\Listener\DoctrineBusMessageEventSubscriber
 */
class DoctrineBusMessageEventSubscriberTest extends TestCase
{
    /**
     * @var DoctrineBusMessageEventSubscriber
     */
    private $doctrineBusMessageEventSubscriber;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TransportTester
     */
    private $transportTester;

    public static function setUpBeforeClass(): void
    {
        static::loadDatabase();

        $messageHolder = new MessageHolder();
        $entityManger = static::getService(EntityManagerInterface::class);
        $entityManger->persist($messageHolder);
        $entityManger->flush();
    }

    public function setUp(): void
    {
        $this->entityManager = static::getService(EntityManagerInterface::class);
        $this->entityManager->clear(); // This is to test postLoad
        $this->transportTester = static::getService('messenger.transport.async.draw.tester');
        $this->transportTester->reset();
        $this->doctrineBusMessageEventSubscriber = static::getService(DoctrineBusMessageEventSubscriber::class);
    }

    public function testPostPersist(): void
    {
        $messageHolder = new MessageHolder();
        $this->entityManager->persist($messageHolder);
        $this->entityManager->flush();

        $this->assertSame(
            [$messageHolder],
            $this->doctrineBusMessageEventSubscriber->getFlattenMessageHolders()
        );
    }

    public function testPostPersistNotMessageHolderEntity(): void
    {
        $notMessageHolder = new NotMessageHolder();
        $this->entityManager->persist($notMessageHolder);
        $this->entityManager->flush();

        $this->assertSame(
            [],
            $this->doctrineBusMessageEventSubscriber->getFlattenMessageHolders()
        );
    }

    public function testPostLoad(): void
    {
        $messageHolder = $this->entityManager->find(MessageHolder::class, 1);

        $this->assertSame(
            [$messageHolder],
            $this->doctrineBusMessageEventSubscriber->getFlattenMessageHolders()
        );
    }

    /**
     * @depends testPostLoad
     */
    public function testOnClearAll(): void
    {
        $this->entityManager->find(MessageHolder::class, 1);
        $this->entityManager->clear();

        $this->assertSame(
            [],
            $this->doctrineBusMessageEventSubscriber->getFlattenMessageHolders()
        );
    }

    /**
     * @depends testPostLoad
     */
    public function testOnClearOther(): void
    {
        $messageHolder = $this->entityManager->find(MessageHolder::class, 1);
        $this->entityManager->clear(NotMessageHolder::class);

        $this->assertSame(
            [$messageHolder],
            $this->doctrineBusMessageEventSubscriber->getFlattenMessageHolders()
        );
    }

    public function testPostFlushEmpty(): void
    {
        $this->entityManager->flush();
        $this->transportTester->assertMessageMatch(AsyncMessageInterface::class, null, 0);
    }

    public function testPostFlushWithOneMessage(): void
    {
        $messageHolder = $this->entityManager->find(MessageHolder::class, 1);
        $messageHolder->messageQueue()->enqueue(new TestMessage());
        $this->entityManager->flush();

        $this->transportTester->assertMessageMatch(AsyncMessageInterface::class);
    }

    public function testPostFlushWithMultipleMessage(): void
    {
        $messageHolder = $this->entityManager->find(MessageHolder::class, 1);
        $messageHolder->messageQueue()->enqueue(new TestMessage());
        $messageHolder->messageQueue()->enqueue(new TestMessage());
        $this->entityManager->flush();

        $this->transportTester->assertMessageMatch(AsyncMessageInterface::class, null, 2);
    }
}
