<?php

namespace Draw\Bundle\SonataIntegrationBundle\Messenger\Admin;

use Doctrine\ORM\QueryBuilder;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\RelativeDateTimeFilter;
use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
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
    private EnvelopeFinderInterface $envelopeFinder;

    private array $queueNames = [];

    /**
     * @required
     */
    public function inject(EnvelopeFinderInterface $envelopeFinder, array $queueNames): void
    {
        $this->envelopeFinder = $envelopeFinder;
        $this->queueNames = $queueNames;
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
            ->add('queueName')
            ->add('createdAt')
            ->add('availableAt')
            ->add('deliveredAt')
            ->add('expiresAt')
            ->add('tags', 'list');
    }

    public function dumpMessage(DrawMessageInterface $message): string
    {
        $envelope = $this->envelopeFinder->findById($message->getMessageId());

        $dumper = new HtmlDumper();
        $dumper->setTheme('light');

        return $dumper->dump((new VarCloner())->cloneVar($envelope), true);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * @param ProxyQueryInterface|QueryBuilder $query
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
}
