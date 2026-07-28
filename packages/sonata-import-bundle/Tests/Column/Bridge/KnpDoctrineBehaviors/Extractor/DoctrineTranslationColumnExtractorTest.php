<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\KnpDoctrineBehaviors\Extractor;

use Draw\Bundle\SonataImportBundle\Column\Bridge\KnpDoctrineBehaviors\Extractor\DoctrineTranslationColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\KnpDoctrineBehaviors\Extractor\Fixtures\TranslatableEntity;
use Draw\Component\Tester\DoctrineOrmTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DoctrineTranslationColumnExtractorTest extends TestCase
{
    use DoctrineOrmTrait;

    private DoctrineTranslationColumnExtractor $object;

    protected function setUp(): void
    {
        $this->object = new DoctrineTranslationColumnExtractor(
            static::createRegistry(
                static::setUpMySqlWithAttributeDriver(
                    [__DIR__.'/Fixtures']
                )
            ),
            ['en', 'fr']
        );
    }

    public function testGetDefaultPriority(): void
    {
        $this->assertSame(
            0,
            $this->object::getDefaultPriority()
        );
    }

    public function testGetOptions(): void
    {
        $this->assertSame(
            [
                'kept',
                'translation#en.label',
                'translation#fr.label',
            ],
            $this->object->getOptions(
                $this->createColumn(),
                ['kept']
            )
        );
    }

    public function testAssign(): void
    {
        $object = new TranslatableEntity();

        $this->assertTrue(
            $this->object
                ->assign(
                    $object,
                    $this->createColumn()
                        ->setMappedTo('translation#fr.label'),
                    'test'
                )
        );

        $this->assertSame(
            'test',
            $object->translate('fr', false)->getLabel()
        );

        $this->assertNull(
            $object->translate('en', false)->getLabel()
        );
    }

    private function createColumn(): Column
    {
        return (new Column())
            ->setImport(
                (new Import())
                    ->setEntityClass(TranslatableEntity::class)
            )
        ;
    }
}
