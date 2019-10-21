<?php namespace Draw\Bundle\CommandBundle\Sonata\Admin;

use Draw\Bundle\CommandBundle\CommandRegistry;
use Draw\Bundle\CommandBundle\Entity\Execution;
use Draw\Bundle\CommandBundle\Listener\CommandFlowListener;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

class ExecutionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    ];

    /**
     * @var CommandRegistry
     */
    private $commandFactory;

    /**
     * @required
     *
     * @var Application
     */
    private $application;

    /**
     * @required
     *
     * @param CommandRegistry $commandFactory
     */
    public function setCommandFactory(CommandRegistry $commandFactory)
    {
        $this->commandFactory = $commandFactory;
    }

    /**
     * @required
     *
     * @param Application $application
     */
    public function setKernel(Application $application)
    {
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        /** @var Execution $execution */
        $execution = parent::getNewInstance();

        if ($this->hasRequest()) {
            if ($this->getRequest()->isMethod(Request::METHOD_GET)) {
                $command = $this->commandFactory->getCommand($this->getRequest()->get('command'));
                $execution->setCommand($command->getName());
                $execution->setCommandName($command->getCommandName());
            }
        }

        return $execution;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id')
            ->add('command')
            ->add('commandName')
            ->add(
                'state',
                null,
                [],
                ChoiceType::class,
                [
                    'choices' => array_combine(
                        Execution::STATES,
                        Execution::STATES
                    )
                ]
            )
            ->add('output')
            ->add('createdAt');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('id')
            ->add('command')
            ->add('commandName')
            ->add('state')
            ->add('createdAt');
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->tab('Execution')
                ->with('General')
                    ->add('id')
                    ->add('command')
                    ->add('commandName')
                    ->add('state')
                    ->add('createdAt')
                    ->add('updatedAt')
                    ->add('input', 'array')
                ->end()
                ->with('Execution')
                    ->add('commandLine', 'text')
                    ->add('outputHtml', 'html')
                ->end()
            ->end();
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('command', null, ['attr' => ['readonly' => true]])
            ->add('commandName', null, ['attr' => ['readonly' => true]]);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->remove('edit');
        $collection->add('acknowledge', $this->getRouterIdParameter().'/acknowledge');
    }

    /**
     * @param $action
     * @param null|Execution $object
     * @return array
     */
    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);

        if ($action == 'show' && $object->getState() == Execution::STATE_ERROR) {
            $list['acknowledge']['template'] = '@DrawSonataCommand\ExecutionAdmin\button_acknowledge.html.twig';
        }

        return $list;
    }

    /**
     * @param Execution $object
     *
     * @return object
     */
    public function create($object)
    {
        $object->setState(Execution::STATE_INITIALIZED);
        $object->setInput([
            'command' => $object->getCommandName(),
            '-vvv' => true,
            '--no-interaction' => true
        ]);

        parent::create($object);

        $this->application->setAutoExit(false);
        $input = new ArrayInput(
            $object->getInput() + ['--' . CommandFlowListener::OPTION_EXECUTION_ID => $object->getId()]
        );
        $output = new BufferedOutput(Output::OUTPUT_NORMAL, true);
        $this->application->run($input, $output);

        return $object;
    }
}