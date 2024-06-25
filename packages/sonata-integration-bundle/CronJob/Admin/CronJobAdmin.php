<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Admin;

use Draw\Component\CronJob\Entity\CronJob;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @method CronJob getSubject()
 */
class CronJobAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('name')
            ->add('command')
            ->add('active');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('command')
            ->add('schedule')
            ->add('active', null, ['editable' => true])
            ->add('timeToLive')
            ->add('executionTimeout')
            ->add('priority')
            ->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'queue' => [
                            'template' => '@DrawSonataIntegration/CronJob/CronJob/list__action_queue.html.twig',
                        ],
                        'delete' => [],
                    ],
                ]
            );
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Definition', ['class' => 'col-md-8'])
                ->add('name')
                ->add('notes')
                ->add(
                    'command',
                    null,
                    [
                        'help' => 'Parameters bag is available. Use like %kernel.project_dir%',
                    ]
                )
                ->add('schedule')
                ->add('active')
                ->add('executionTimeout')
            ->end()
            ->with('Queue Configuration', ['class' => 'col-md-4'])
                ->add('timeToLive')
                ->add('priority')
            ->end();
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var CronJobExecutionAdmin $executionAdmin */
        $executionAdmin = $this->getConfigurationPool()->getAdminByAdminCode(CronJobExecutionAdmin::class);

        $show
            ->add('name')
            ->add('notes')
            ->add('command')
            ->add('schedule')
            ->add('active')
            ->add('executionTimeout')
            ->add('timeToLive')
            ->add('priority')
            ->ifTrue(!$this->getSubject()->getExecutions()->isEmpty())
                ->add(
                    'recentExecutions',
                    'grid',
                    [
                        'fieldValueOnly' => false,
                        'colspan' => true,
                        'fieldsAdmin' => $executionAdmin,
                        'fields' => $executionAdmin->configureGridFields([]),
                    ]
                )
            ->ifEnd();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('queue', sprintf('%s/queue', $this->getRouterIdParameter()));
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if ('show' === $action) {
            $buttonList['queue'] = [
                'template' => '@DrawSonataIntegration/CronJob/CronJob/show__action_queue.html.twig',
            ];
        }

        return $buttonList;
    }
}
