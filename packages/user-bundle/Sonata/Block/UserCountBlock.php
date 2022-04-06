<?php

namespace Draw\Bundle\UserBundle\Sonata\Block;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class UserCountBlock extends AbstractBlockService
{
    private string $adminCode;

    protected Pool $pool;

    public function __construct(Environment $twig, Pool $pool, string $userAdminCode)
    {
        $this->adminCode = $userAdminCode;
        $this->pool = $pool;
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));

        $datagrid = $admin->getDatagrid();

        $filters = $blockContext->getSetting('filters');

        if (!isset($filters['_per_page'])) {
            $filters['_per_page'] = ['value' => $blockContext->getSetting('limit')];
        }

        foreach ($filters as $name => $data) {
            $datagrid->setValue($name, $data['type'] ?? null, $data['value']);
        }

        $datagrid->buildPager();

        return $this->renderPrivateResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'admin_pool' => $this->pool,
            'admin' => $admin,
            'pager' => $datagrid->getPager(),
            'datagrid' => $datagrid,
        ], $response);
    }

    public function getName(): string
    {
        return 'User Count';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon' => 'fas fa-user',
            'text' => 'Total users',
            'translation_domain' => null,
            'color' => 'bg-aqua',
            'code' => $this->adminCode,
            'filters' => [],
            'limit' => 1000,
            'template' => '@SonataAdmin/Block/block_stats.html.twig',
        ]);
    }
}
