<?php

namespace Draw\Bundle\MessengerBundle\Sonata\Admin;

use Draw\Bundle\MessengerBundle\Entity\BaseMessengerMessage;
use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
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

    public function inject(
        ContainerInterface $receiverLocator,
        array $transportMapping
    ): void {
        $this->receiverLocator = $receiverLocator;
        $this->transportMapping = $transportMapping;
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
                    ],
                ],
            );
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('queueName')
            ->add('createdAt')
            ->add('availableAt')
            ->add('deliveredAt');
    }

    public function dumpMessage(BaseMessengerMessage $message): string
    {
        $transportName = $this->transportMapping[$message->queueName] ?? null;

        /** @var ListableReceiverInterface $receiver */
        $receiver = $this->receiverLocator->get($transportName);

        $dumper = new HtmlDumper();
        $dumper->setTheme('light');

        return $dumper->dump((new VarCloner())->cloneVar($receiver->find($message->id)), true);
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
