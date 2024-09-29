<?php

namespace App\Tests\SonataImportBundle;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\DeleteTemporaryEntity\BaseTemporaryEntityCleaner;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class ImporterTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireService]
    private Importer $importer;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    public function testImport(): void
    {
        $fileName = tempnam(sys_get_temp_dir(), 'importer-test');

        $file = fopen($fileName, 'w+');

        register_shutdown_function('unlink', $fileName);

        fputcsv($file, ['name', 'active', 'translation#en.label', 'translation#fr.label']);

        fputcsv($file, [$name = 'test'.uniqid(), '1', 'testEn', 'testFr']);

        $import = (new Import())
            ->setEntityClass(Tag::class)
            ->setInsertWhenNotFound(false)
        ;

        $this->importer->buildFromFile(
            $import,
            new \SplFileInfo($fileName)
        );

        $this->importer
            ->processImport($import)
        ;

        $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => $name]);

        static::assertNull(
            $tag,
            'Should not have been created because insertWhenNotFound is false'
        );

        $import->setInsertWhenNotFound(true);

        $this->importer
            ->processImport($import)
        ;

        $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => $name]);

        static::assertInstanceOf(
            Tag::class,
            $tag
        );

        BaseTemporaryEntityCleaner::$temporaryEntities[] = $tag;

        static::assertSame(
            $name,
            $tag->getName()
        );

        static::assertTrue(
            $tag->getActive()
        );

        static::assertSame(
            'testEn',
            $tag->translate('en')->getLabel()
        );

        static::assertSame(
            'testFr',
            $tag->translate('fr')->getLabel()
        );
    }
}
