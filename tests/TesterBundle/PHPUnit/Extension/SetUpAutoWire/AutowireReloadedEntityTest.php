<?php

namespace App\Tests\TesterBundle\PHPUnit\Extension\SetUpAutoWire;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\DeleteTemporaryEntity\BaseTemporaryEntityCleaner;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireReloadedEntity;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class AutowireReloadedEntityTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireReloadedEntity]
    private static ?Tag $tag = null;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    #[DoesNotPerformAssertions]
    public function testCreate(): void
    {
        BaseTemporaryEntityCleaner::$temporaryEntities[] = $tag = (new Tag())
            ->setName('AutowireReloadedEntity')
        ;

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        self::$tag = $tag;
    }

    #[Depends('testCreate')]
    public function testReload(): void
    {
        static::assertInstanceOf(Tag::class, self::$tag);

        static::assertSame(
            'AutowireReloadedEntity',
            self::$tag->getName()
        );
    }
}
