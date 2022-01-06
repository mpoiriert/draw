<?php

namespace Draw\Bundle\MessengerBundle\Sonata\Admin;

use Draw\Bundle\MessengerBundle\Entity\DrawMessageInterface;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageTrait;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\RelativeDateTimeFilter;
use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

abstract class MessengerMessageAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    private $transportMapping;

    /**
     * @var ContainerInterface
     */
    private $receiverLocator;

    private $supportDrawTransport;

    public function __construct($code, $class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->supportDrawTransport = $this->supportDrawTransport();
    }

    public function inject(
        ContainerInterface $receiverLocator,
        array $transportMapping
    ): void {
        $this->receiverLocator = $receiverLocator;
        $this->transportMapping = $transportMapping;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'availableAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
    }

    private function supportDrawTransport(): bool
    {
        if (null === $this->supportDrawTransport) {
            $class = $this->getClass();
            $traits = [];
            do {
                $traits = array_merge(class_uses($class), $traits);
            } while ($class = get_parent_class($class));

            foreach ($traits as $trait => $same) {
                $traits = array_merge(class_uses($trait), $traits);
            }

            $this->supportDrawTransport = in_array(DrawMessageTrait::class, array_unique($traits));
        }

        return $this->supportDrawTransport;
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
                            array_keys($this->transportMapping),
                            array_keys($this->transportMapping),
                        ),
                        'multiple' => true,
                    ],
                ],
            );

        if ($this->supportDrawTransport) {
            $filter->add(
                'tags.name',
                null,
                [
                    'show_filter' => true,
                ]
            );
        }

        if (class_exists(RelativeDateTimeFilter::class)) {
            $filter->add(
                'availableAt',
                RelativeDateTimeFilter::class,
                [
                    'show_filter' => true,
                ]
            );

            $filter->add(
                'deliveredAt',
                RelativeDateTimeFilter::class,
            );

            if ($this->supportDrawTransport) {
                $filter->add(
                    'expiresAt',
                    RelativeDateTimeFilter::class,
                );
            }
        }
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('queueName')
            ->add('createdAt')
            ->add('availableAt')
            ->add('deliveredAt');

        if ($this->supportDrawTransport) {
            $list
                ->add('expiresAt')
                ->add('tags');
        }
    }

    public function dumpMessage(DrawMessageInterface $message): string
    {
        $transportName = $this->transportMapping[$message->getQueueName()] ?? null;

        /** @var ListableReceiverInterface $receiver */
        $receiver = $this->receiverLocator->get($transportName);

        $dumper = new HtmlDumper();
        $dumper->setTheme('light');

        return $dumper->dump((new VarCloner())->cloneVar($receiver->find($message->getMessageId())), true);
    }

    /**
     * @param RouteCollection|RouteCollectionInterface $collection
     */
    protected function backwardCompatibleConfigureRoute($collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
    }
}
