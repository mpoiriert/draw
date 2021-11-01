<?php

namespace Draw\Bundle\SonataExtraBundle\Block;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\BlockBundle\Block\BlockContextInterface;

trait BlockFilterTrait
{
    private function buildFilter(AdminInterface $admin, BlockContextInterface $blockContext): DatagridInterface
    {
        $admin->setPagerType(Pager::TYPE_DEFAULT);

        $dataGrid = $admin->getDatagrid();

        $filters = $blockContext->getSettings()['filters'] ?? [];

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = ['value' => $blockContext->getSetting('limit')];
        }

        foreach ($filters as $name => $data) {
            $dataGrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $dataGrid->buildPager();

        return $dataGrid;
    }
}
