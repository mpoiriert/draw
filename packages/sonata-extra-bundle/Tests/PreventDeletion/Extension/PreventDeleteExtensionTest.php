<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\PreventDeletion\Extension;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataExtraBundle\PreventDelete\Extension\PreventDeleteExtension;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Security\Core\Security;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @internal
 */
class PreventDeleteExtensionTest extends TestCase
{
    private PreventDeleteExtension $object;

    private PreventDeleteRelationLoader&MockObject $preventDeleteRelationLoader;

    private Security&MockObject $security;

    protected function setUp(): void
    {
        $this->object = new PreventDeleteExtension(
            $this->preventDeleteRelationLoader = $this->createMock(PreventDeleteRelationLoader::class),
            $this->createMock(ManagerRegistry::class),
            $this->security = $this->createMock(Security::class),
        );
    }

    public function testConfigureShowFieldsNoAccess(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->object,
            'restrictToRole',
            'ROLE_ADMIN'
        );

        $this->security
            ->expects(static::once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false)
        ;

        $this->preventDeleteRelationLoader
            ->expects(static::never())
            ->method('getRelationsForObject')
        ;

        $showMapper = new ShowMapper(
            $this->createMock(ShowBuilderInterface::class),
            new FieldDescriptionCollection(),
            $this->createMock(AdminInterface::class),
        );

        $this->object->configureShowFields($showMapper);
    }
}
