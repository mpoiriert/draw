<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete\Extension;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDelete;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    'sonata.admin.extension',
    [
        'global' => true,
    ]
)]
class PreventDeleteExtension extends AbstractAdminExtension
{
    public function __construct(
        private PreventDeleteRelationLoader $preventDeleteRelationLoader,
        private ManagerRegistry $managerRegistry
    ) {
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $relations = $this->preventDeleteRelationLoader->getRelationsForObject($show->getAdmin()->getSubject());

        if (empty($relations)) {
            return;
        }

        $admin = $show->getAdmin();

        while ($show->hasOpenTab()) {
            $show->end();
        }

        $show
            ->tab('prevent_delete');

        $configurationPool = $admin->getConfigurationPool();

        foreach ($relations as $relation) {
            $metadata = $relation->getMetadata();

            $maxResult = $metadata['max_results'] ?? 10;
            $subject = $admin->getSubject();
            $relatedEntities = $relation->getEntities(
                $this->managerRegistry,
                $subject,
                $maxResult + 1
            );

            if (empty($relatedEntities)) {
                continue;
            }

            $relatedAdmin = $configurationPool->hasAdminByClass($relation->getRelatedClass())
                ? $configurationPool->getAdminByClass($relation->getRelatedClass())
                : null;

            $filterUrl = null;
            if (\count($relatedEntities) > $maxResult) {
                $relatedEntities = \array_slice($relatedEntities, 0, $maxResult);
                $filterUrl = $this->getFilterParameters(
                    $relatedAdmin,
                    $relation,
                    $subject,
                );
            }

            $show
                ->with(
                    'prevent_delete_'.$relation->getRelatedClass(),
                    [
                        'class' => 'col-sm-6',
                        'label' => $relatedAdmin->getClassnameLabel(),
                    ]
                )
                    ->add(
                        'prevent_delete_'.$relation->getRelatedClass().'_path'.$relation->getPath(),
                        null,
                        [
                            'virtual_field' => true,
                            'label' => $metadata['path_label'] ?? $relation->getPath(),
                            'template' => '@DrawSonataExtra/CRUD/show_prevent_delete.html.twig',
                            'relation' => $relation,
                            'related_admin' => $relatedAdmin,
                            'related_entities' => $relatedEntities,
                            'filter_url' => $filterUrl,
                        ]
                    )
                ->end();
        }

        $show
            ->end();
    }

    private function getFilterParameters(
        AdminInterface $admin,
        PreventDelete $preventDelete,
        object $subject
    ): ?string {
        if (!$admin->getDatagrid()->hasFilter($preventDelete->getPath())) {
            return null;
        }

        $filter = $admin->getDatagrid()->getFilter($preventDelete->getPath());

        $value = $subject->getId();
        if ($filter->getFieldOption('multiple', false)) {
            $value = [$subject->getId()];
        }

        return $admin->generateUrl(
            'list',
            [
                'filter' => [
                    $filter->getFormName() => [
                        'value' => $value,
                    ],
                ],
            ]
        );
    }
}
