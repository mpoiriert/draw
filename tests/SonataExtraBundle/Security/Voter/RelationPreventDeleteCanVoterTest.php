<?php

namespace App\Tests\SonataExtraBundle\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\SonataExtraBundle\PreventDelete\Security\Voter\PreventDeleteVoter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class RelationPreventDeleteCanVoterTest extends KernelTestCase
{
    private PreventDeleteVoter $object;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->object = static::getContainer()->get(PreventDeleteVoter::class);

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testVoteNoSubject(): void
    {
        static::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->object->vote(
                $this->createMock(TokenInterface::class),
                null,
                ['SONATA_CAN_DELETE']
            )
        );
    }

    public function testVoteNotProperSubject(): void
    {
        static::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->object->vote(
                $this->createMock(TokenInterface::class),
                new User(),
                ['SONATA_CAN_DELETE']
            )
        );
    }

    public function testVoteWithRelationOtherAttribute(): void
    {
        static::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->object->vote(
                $this->createMock(TokenInterface::class),
                $this->entityManager->getRepository(Tag::class)->findOneBy(['label' => 'Admin']),
                ['SONATA_CAN_CREATE']
            )
        );
    }

    public function testVoteWithRelation(): void
    {
        static::assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->object->vote(
                $this->createMock(TokenInterface::class),
                $this->entityManager->getRepository(Tag::class)->findOneBy(['label' => 'Admin']),
                ['SONATA_CAN_DELETE']
            )
        );
    }

    public function testVoteNoRelation(): void
    {
        static::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->object->vote(
                $this->createMock(TokenInterface::class),
                $this->entityManager->getRepository(Tag::class)->findOneBy(['label' => 'NotUse']),
                ['SONATA_CAN_DELETE']
            )
        );
    }
}
