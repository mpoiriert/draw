<?php

namespace Draw\Bundle\SonataExtraBundle\EventListener;

use Draw\Bundle\SonataExtraBundle\Block\Event\FinalizeContextEvent;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminMonitoringListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FinalizeContextEvent::class => 'finalizeContext',
        ];
    }

    public function __construct(private Pool $pool)
    {
    }

    public function finalizeContext(FinalizeContextEvent $event): void
    {
        $blockContext = $event->getBlockContext();

        $adminConfiguration = $blockContext->getSetting('extra_data')['admin'] ?? null;

        if (!$adminConfiguration) {
            return;
        }

        $event->stopPropagation();

        $admin = $this->pool->getAdminByAdminCode($adminConfiguration['code']);
        $filters = $adminConfiguration['filters'] ?? [];

        $dataGrid = $this->buildFilter(
            $admin,
            $filters,
            $adminConfiguration['limit'] ?? null
        );

        $count = $dataGrid->getPager()->countResults();

        $blockContext->setSetting('count', $count);
        if (!$blockContext->getSetting('link_label')) {
            $blockContext->setSetting('link_label', 'stats_view_more');
        }

        $blockContext->setSetting('link', $admin->generateUrl('list', ['filter' => $filters]));
    }

    private function buildFilter(AdminInterface $admin, array $filters, ?int $limit = null): DatagridInterface
    {
        $admin->setPagerType(Pager::TYPE_DEFAULT);

        $dataGrid = $admin->getDatagrid();

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = ['value' => $limit];
        }

        foreach ($filters as $name => $data) {
            $dataGrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $dataGrid->buildPager();

        return $dataGrid;
    }
}
