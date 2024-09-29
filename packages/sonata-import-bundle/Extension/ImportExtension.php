<?php

namespace Draw\Bundle\SonataImportBundle\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['sonata.admin.extension'])]
class ImportExtension extends AbstractAdminExtension
{
    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null,
    ): array {
        if ('list' === $action) {
            $list['import']['template'] = '@DrawSonataImport\ImportAdmin\button_import.html.twig';
        }

        return $list;
    }
}
