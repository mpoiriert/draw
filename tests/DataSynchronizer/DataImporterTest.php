<?php

namespace App\Tests\DataSynchronizer;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\DataSynchronizer\Artefact;
use Draw\Component\DataSynchronizer\Event\PreDeleteEntityEvent;
use Draw\Component\DataSynchronizer\Export\DataExporter;
use Draw\Component\DataSynchronizer\Import\DataImporter;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class DataImporterTest extends KernelTestCase implements AutowiredInterface
{
    private const TAG_NAME = 'imported';

    #[AutowireService]
    private DataImporter $dataImporter;

    #[AutowireService]
    private DataExporter $dataExporter;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    #[AutowireService]
    private EventDispatcherInterface $eventDispatcher;

    #[
        BeforeClass,
        AfterClass,
    ]
    public static function cleanUp(): void
    {
        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getConnection()
            ->executeStatement(
                'DELETE FROM draw_acme__tag WHERE name = :name',
                [
                    'name' => self::TAG_NAME,
                ]
            )
        ;
    }

    public function testImportAddEntity(): void
    {
        $tagRepository = $this->entityManager
            ->getRepository(Tag::class)
        ;

        static::assertNull(
            $tagRepository->findOneBy(['name' => self::TAG_NAME])
        );

        $previousCount = $tagRepository->count([]);

        $file = $this->dataExporter->export();

        register_shutdown_function('unlink', $file);

        $data = $this->getTagData($file);

        $data[] = [
            'name' => self::TAG_NAME,
            'active' => false,
        ];

        $this->replaceTagData(
            $file,
            $data,
        );

        $this->dataImporter->import($file);

        static::assertNotNull(
            $tagRepository->findOneBy(['name' => self::TAG_NAME])
        );

        static::assertSame(
            $previousCount + 1,
            $tagRepository->count([])
        );
    }

    #[Depends('testImportAddEntity')]
    public function testImportRemoveEntity(): void
    {
        $tagRepository = $this->entityManager
            ->getRepository(Tag::class)
        ;

        static::assertNotNull(
            $tagRepository->findOneBy(['name' => self::TAG_NAME])
        );

        $previousCount = $tagRepository->count([]);

        $file = $this->dataExporter->export();

        register_shutdown_function('unlink', $file);

        $data = $this->getTagData($file);

        foreach ($data as $index => $datum) {
            if (self::TAG_NAME === $datum['name']) {
                unset($data[$index]);
            }
        }

        $this->replaceTagData(
            $file,
            $data,
        );

        $this->dataImporter->import($file);

        static::assertNull(
            $tagRepository->findOneBy(['name' => self::TAG_NAME])
        );

        static::assertSame(
            $previousCount - 1,
            $tagRepository->count([])
        );
    }

    #[Depends('testImportRemoveEntity')]
    public function testImportRemoveAllTagsIgnored(): void
    {
        $tagRepository = $this->entityManager
            ->getRepository(Tag::class)
        ;

        $previousCount = $tagRepository->count([]);

        $file = $this->dataExporter->export();

        register_shutdown_function('unlink', $file);

        $this->replaceTagData(
            $file,
            [],
        );

        $this->eventDispatcher
            ->addListener(
                PreDeleteEntityEvent::class,
                static function (PreDeleteEntityEvent $event): void {
                    $entity = $event->getEntity();

                    if (!$entity instanceof Tag) {
                        return;
                    }

                    $event->preventDelete();
                }
            )
        ;

        $this->dataImporter->import($file);

        static::assertSame(
            $previousCount,
            $tagRepository->count([]),
        );
    }

    private function getTagData(string $file): array
    {
        $artefact = Artefact::loadFromFile($file);

        $data = $artefact->jsonDecodeFromName('data/App_Entity_Tag.json')[Tag::class];

        $artefact->close();

        return $data;
    }

    private function replaceTagData(string $file, array $data): void
    {
        $data = array_values($data);

        $artefact = Artefact::loadFromFile($file);

        $artefact->deleteName('data/App_Entity_Tag.json');
        $artefact->addClassData(Tag::class, $data);

        $artefact->finalize();
    }
}
