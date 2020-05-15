<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\DashboardBundle\Annotations\ActionCreate;
use Draw\Bundle\DashboardBundle\Annotations\ActionEdit;
use Draw\Bundle\DashboardBundle\Annotations\ActionList;
use Draw\Bundle\DashboardBundle\Annotations\Column;
use Draw\Bundle\DashboardBundle\Annotations\ConfirmFlow;
use Draw\Bundle\DashboardBundle\Annotations\Filter;
use Draw\Bundle\DashboardBundle\Annotations\FormFlow;
use Draw\Bundle\DashboardBundle\Annotations\FormInput;
use Draw\Bundle\DashboardBundle\Annotations\FormInputChoices;
use Draw\Bundle\DashboardBundle\Annotations\FormInputCollection;
use Draw\Bundle\DashboardBundle\Annotations\FormInputComposite;
use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Twig\Environment;

class OptionActionListener implements EventSubscriberInterface
{
    private $twig;

    private $expressionLanguage;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    private $serializer;

    /**
     * @var OptionBuilderEvent
     */
    private $currentEvent;

    public function __construct(
        Environment $environment,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer
    ) {
        $this->twig = $environment;
        $this->managerRegistry = $managerRegistry;
        $this->serializer = $serializer;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public static function getSubscribedEvents()
    {
        return [
            OptionBuilderEvent::class => [
                ['buildOption'],
                ['buildOptionForList'],
                ['buildOptionForCreateEdit'],
            ]
        ];
    }

    public function buildOption(OptionBuilderEvent $event)
    {
        $action = $event->getAction();

        if (null === ($flow = $action->getFlow())) {
            return;
        }

        $request = $event->getRequest();

        if($flow instanceof FormFlow) {
            if(!$flow->getId()) {
                $flow->setId(uniqid());
            }
        }

        if ($flow instanceof ConfirmFlow) {
            $flow->setMessage(
                $this->renderStringTemplate(
                    $flow->getMessage(),
                    $request->attributes->all()
                )
            );

            $flow->setTitle(
                $this->renderStringTemplate(
                    $flow->getTitle(),
                    $request->attributes->all()
                )
            );
        }
    }

    public function buildOptionForList(OptionBuilderEvent $event)
    {
        $action = $event->getAction();
        if (!$action instanceof ActionList) {
            return;
        }

        $operation = $event->getOperation();

        switch (true) {
            case !isset($operation->responses[200]):
            case null === ($responseSchema = $operation->responses[200]->schema):
            case !isset($responseSchema->properties['data']):
                return;
        }

        $openApiSchema = $event->getOpenApiSchema();
        $item = $openApiSchema->resolveSchema($responseSchema->properties['data']->items);

        $columns = [];
        $filters = [];
        foreach ($item->properties as $property) {
            $column = $property->vendor['x-draw-dashboard-column'] ?? null;
            $columnPosition = 0;
            if ($column instanceof Column) {
                if ($column->getLabel() === null) {
                    $column->setLabel($column->getId());
                }
                if ($column->getPosition() !== null) {
                    $columnPosition = $column->getPosition();
                }
                $columns[$columnPosition][] = $column;
            }

            $filter = $property->vendor['x-draw-dashboard-filter'] ?? null;
            if ($filter instanceof Filter) {
                if ($input = $filter->getInput()) {
                    if ($input->getId() === null) {
                        $input->setId($filter->getId());
                    }
                    $this->configureInput($input, $item, $property, $openApiSchema);
                }
                null !== ($filterPosition = $filter->getPosition()) || ($filterPosition = $columnPosition);
                $filters[$filterPosition][] = $filter;
            }
        }

        if ($filters) {
            ksort($filters);
            $filters = call_user_func_array('array_merge', $filters);
        }

        if ($columns) {
            ksort($columns);
            $columns = call_user_func_array('array_merge', $columns);
        }

        $columns[] = new Column(['id' => '_actions', 'type' => 'actions', 'label' => 'actions']);

        $action->setColumns($columns);
        $action->setFilters($filters);
    }

    public function buildOptionForCreateEdit(OptionBuilderEvent $event)
    {
        $action = $event->getAction();
        if (!$action instanceof ActionCreate && !$action instanceof ActionEdit) {
            return;
        }

        $operation = $event->getOperation();

        $bodyParameter = null;
        foreach ($operation->parameters as $parameter) {
            if ($parameter instanceof BodyParameter) {
                $bodyParameter = $parameter;
                break;
            }
        }

        if (!$bodyParameter) {
            return;
        }

        $openApiSchema = $event->getOpenApiSchema();
        $item = $openApiSchema->resolveSchema($bodyParameter->schema);

        foreach ($this->loadFormInputs($item, $openApiSchema) as $key => $value) {
            $action->{'set' . $key}($value);
        }
    }

    private function loadFormInputs(Schema $objectSchema, Root $openApiSchema)
    {
        $objectSchema = $openApiSchema->resolveSchema($objectSchema);
        $inputs = [];
        foreach ($objectSchema->properties as $property) {
            $input = $property->vendor['x-draw-dashboard-form-input'] ?? null;
            if (!$input instanceof FormInput) {
                continue;
            }

            $this->configureInput($input, $objectSchema, $property, $openApiSchema);

            $inputs[] = $input;
        }

        $default = (new \ReflectionClass($objectSchema->getVendorData()['x-draw-dashboard-class-name']))->newInstance();
        return compact('inputs', 'default');
    }

    private function configureInput(FormInput $input, Schema $objectSchema, Schema $property, Root $openApiSchema)
    {
        if (!$input->getLabel()) {
            $input->setLabel($input->getId());
        }

        if ($input instanceof FormInputChoices && $input->getChoices() === null) {
            $input->setChoices($this->loadChoices($input, $objectSchema, $property, $openApiSchema));
            $input->setSourceCompareKeys(['id']); //todo make this dynamic
        }

        if ($input instanceof FormInputComposite && $input->getSubForm() === null) {
            $input->setSubForm($this->loadSubForm($input, $objectSchema, $property, $openApiSchema));
        }

        if ($input instanceof FormInputCollection) {
            $input->setSubForm($this->loadSubForm(
                $input,
                $objectSchema,
                $openApiSchema->resolveSchema($property->items),
                $openApiSchema
            ));
        }
    }

    private function loadSubForm(FormInput $input, Schema $schema, Schema $property, Root $openApiSchema)
    {
        return $this->loadFormInputs($property, $openApiSchema);
    }

    private function loadChoices(FormInputChoices $input, Schema $schema, Schema $property, Root $openApiSchema)
    {
        $expression = $input->getExpression();

        $values = [
            'input' => $input,
        ];

        if ($property->items !== null) {
            $target = $openApiSchema->resolveSchema($property->items);
        } else {
            $target = $openApiSchema->resolveSchema($property);
        }

        $targetClass = $target->getVendorData()['x-draw-dashboard-class-name'];

        if($manager = $this->managerRegistry->getManagerForClass($targetClass)) {
            $repository = $manager->getRepository($targetClass);
            $values['repository'] = $repository;
            if(!$expression) {
                $expression = 'repository.findAll()';
            }
        }

        $objects = $this->expressionLanguage->evaluate($expression, $values);

        $choices = [];
        foreach ($objects as $object) {
            $choices[] = [
                'label' => (string)$object,
                'value' => [
                    'id' => $object->getId()//todo make this dynamic
                ]
            ];
        }

        return $choices;
    }

    private function renderStringTemplate($template, array $context)
    {
        if (!$template) {
            return '';
        }

        return $this->twig->render(
            $this->twig->createTemplate($template),
            $context
        );
    }
}