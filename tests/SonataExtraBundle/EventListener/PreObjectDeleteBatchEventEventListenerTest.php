<?php

namespace App\Tests\SonataExtraBundle\EventListener;

use App\Entity\User;
use App\Security\Voter\CannotSelfVoter;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\SonataExtraBundle\EventListener\PreObjectDeleteBatchEventEventListener;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Sonata\DoctrineORMAdminBundle\Event\PreObjectDeleteBatchEvent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PreObjectDeleteBatchEventEventListenerTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireService]
    private PreObjectDeleteBatchEventEventListener $object;

    #[AutowireService]
    private TokenStorageInterface $tokenStorage;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    public function testHandlePreObjectDeleteBatchEventCannotDelete(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $this->connectUser($user);

        $this->object->handlePreObjectDeleteBatchEvent(
            $event = new PreObjectDeleteBatchEvent(
                User::class,
                $user
            )
        );

        static::assertFalse(
            $event->shouldDelete(),
            CannotSelfVoter::class.' should prevent deletion of the user.'
        );
    }

    public function testHandlePreObjectDeleteBatchEventCanDelete(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $this->connectUser($user);

        $this->object->handlePreObjectDeleteBatchEvent(
            $event = new PreObjectDeleteBatchEvent(
                User::class,
                new User()
            )
        );

        static::assertTrue(
            $event->shouldDelete()
        );
    }

    private function connectUser(User $user): void
    {
        $this->tokenStorage
            ->setToken(
                new class($user) extends AbstractToken {
                    public function __construct(User $user)
                    {
                        parent::__construct(['ROLE_SUPER_ADMIN']);

                        $this->setUser($user);
                    }

                    public function getCredentials()
                    {
                        return null;
                    }
                }
            );
    }
}
