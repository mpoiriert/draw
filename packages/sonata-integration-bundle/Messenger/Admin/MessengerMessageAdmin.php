<?php

namespace Draw\Bundle\SonataIntegrationBundle\Messenger\Admin;

use Doctrine\ORM\QueryBuilder;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\RelativeDateTimeFilter;
use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class MessengerMessageAdmin extends AbstractAdmin
{
    public function __construct(private EnvelopeFinderInterface $envelopeFinder, private array $queueNames)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'availableAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add(
                'queueName',
                ChoiceFilter::class,
                [
                    'show_filter' => true,
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'choices' => array_combine(
                            $this->queueNames,
                            $this->queueNames
                        ),
                        'multiple' => true,
                    ],
                ],
            )
            ->add(
                'tags.name',
                null,
                [
                    'show_filter' => true,
                ]
            )
            ->add(
                'availableAt',
                RelativeDateTimeFilter::class,
                [
                    'show_filter' => true,
                ]
            )
            ->add(
                'messageClass'
            )
            ->add(
                'body'
            )
            ->add(
                'deliveredAt',
                RelativeDateTimeFilter::class,
            )
            ->add(
                'expiresAt',
                RelativeDateTimeFilter::class,
            );
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('messageClass')
            ->add('queueName')
            ->add('createdAt')
            ->add('availableAt')
            ->add('deliveredAt')
            ->add('expiresAt')
            ->add('tags', 'list')
            ->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'actions' => [
                        'show' => [],
                        'retry' => [
                            'template' => '@DrawSonataIntegration/Messenger/Message/list__action_retry.html.twig',
                        ],
                        'delete' => [],
                    ],
                ]
            );
    }

    public function dumpMessage(DrawMessageInterface $message): string
    {
        try {
            $envelope = $this->envelopeFinder->findById($message->getMessageId());
        } catch (MessageNotFoundException) {
            $envelope = null;
        }

        $dumper = new HtmlDumper();
        $dumper->setTheme('light');

        return $dumper->dump((new VarCloner())->cloneVar($envelope), true);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('retry', sprintf('%s/retry', $this->getRouterIdParameter()));
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * @param ProxyQueryInterface&QueryBuilder $query
     */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->andWhere(
                $query->expr()
                    ->orX(
                        sprintf('%s.expiresAt <= :now', $query->getRootAliases()[0]),
                        sprintf('%s.expiresAt IS NULL', $query->getRootAliases()[0]),
                    )
            )
            ->setParameter('now', new \DateTimeImmutable());

        return $query;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if ('show' === $action && 'failed' === $object?->getQueueName()) {
            $buttonList['retry'] = [
                'template' => '@DrawSonataIntegration/Messenger/Message/show__action_retry.html.twig',
            ];
        }

        return $buttonList;
    }
}
