<?php

namespace Draw\Bundle\DashboardBundle\Listener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\DashboardBundle\Action\ActionBuilder;
use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Annotations\ActionCreate;
use Draw\Bundle\DashboardBundle\Annotations\ActionEdit;
use Draw\Bundle\DashboardBundle\Annotations\ActionList;
use Draw\Bundle\DashboardBundle\Annotations\CanBeExcludeInterface;
use Draw\Bundle\DashboardBundle\Annotations\Column;
use Draw\Bundle\DashboardBundle\Annotations\ConfirmFlow;
use Draw\Bundle\DashboardBundle\Annotations\Filter;
use Draw\Bundle\DashboardBundle\Annotations\FormFlow;
use Draw\Bundle\DashboardBundle\Annotations\FormInput;
use Draw\Bundle\DashboardBundle\Annotations\FormInputAutoComplete;
use Draw\Bundle\DashboardBundle\Annotations\FormInputChoices;
use Draw\Bundle\DashboardBundle\Annotations\FormInputCollection;
use Draw\Bundle\DashboardBundle\Annotations\FormInputComposite;
use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Draw\Bundle\DashboardBundle\ExpressionLanguage\ExpressionLanguage;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class OptionActionListener implements EventSubscriberInterface
{
    private $actionFinder;

    private $twig;

    private $expressionLanguage;

    private $actionBuilder;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    private $serializer;

    private $urlGenerator;

    public function __construct(
        Environment $environment,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        ExpressionLanguage $expressionLanguage,
        UrlGeneratorInterface $urlGenerator,
        ActionFinder $actionFinder,
        ActionBuilder $actionBuilder
    ) {
        $this->twig = $environment;
        $this->managerRegistry = $managerRegistry;
        $this->serializer = $serializer;
        $this->expressionLanguage = $expressionLanguage;
        $this->urlGenerator = $urlGenerator;
        $this->actionFinder = $actionFinder;
        $this->actionBuilder = $actionBuilder;
    }

    public static function getSubscribedEvents()
    {
        return [
            OptionBuilderEvent::class => [
                ['buildOption'],
                ['buildOptionForList'],
                ['buildOptionForCreateEdit'],
            ],
        ];
    }

    public function buildOption(OptionBuilderEvent $event)
    {
        $action = $event->getAction();

        if (null === ($flow = $action->getFlow())) {
            return;
        }

        $request = $event->getRequest();

        if ($flow instanceof FormFlow) {
            if (!$flow->getId()) {
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
        $objectSchema = $openApiSchema->resolveSchema($responseSchema->properties['data']->items);

        $columns = [];
        $filters = [];
        foreach ($objectSchema->properties as $propertySchema) {
            $columnPosition = 0;
            if ($column = $this->processColumn($openApiSchema, $objectSchema, $propertySchema)) {
                if (null !== $column->getPosition()) {
                    $columnPosition = $column->getPosition();
                }
                $columns[$columnPosition][] = $column;
            }

            if ($filter = $this->processFilter($openApiSchema, $objectSchema, $propertySchema)) {
                $filterPosition = $columnPosition;
                if (null !== $filter->getPosition()) {
                    $filterPosition = $filter->getPosition();
                }
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

        $targetActions = $this->actionFinder->findAllByByTarget($objectSchema->getVendorData()['x-draw-dashboard-class-name']);

        $targetActions = array_filter($targetActions, function (Action $targetAction) {
            return ActionList::TYPE != $targetAction->getType();
        });

        if (!$targetActions) {
            return;
        }

        $actions = $this->actionBuilder->buildActions($targetActions);
        $action->setCollectionActions($actions);
    }

    private function processFilter(Root $openApiSchema, Schema $objectSchema, Schema $propertySchema): ?Filter
    {
        $filter = $propertySchema->vendor['x-draw-dashboard-filter'] ?? null;
        if (!$filter instanceof Filter) {
            return null;
        }

        if ($input = $filter->getInput()) {
            if (null === $input->getId()) {
                $input->setId($filter->getId());
            }
            $this->configureInput($input, $objectSchema, $propertySchema, $openApiSchema);
        }

        $values = compact('filter', 'objectSchema', 'propertySchema');

        if ($this->shouldBeExcluded($filter, $values)) {
            return null;
        }

        return $filter;
    }

    private function processColumn(Root $openApiSchema, Schema $objectSchema, Schema $propertySchema): ?Column
    {
        $column = $propertySchema->vendor['x-draw-dashboard-column'] ?? null;
        if (!$column instanceof Column) {
            return null;
        }

        if (null === $column->getLabel()) {
            $column->setLabel($column->getId());
        }

        $values = compact('column', 'propertySchema', 'objectSchema');

        if ($this->shouldBeExcluded($column, $values)) {
            return null;
        }

        return $column;
    }

    private function shouldBeExcluded(CanBeExcludeInterface $object, $values): bool
    {
        if (!$object->getExcludeIf()) {
            return false;
        }

        return $this->expressionLanguage->evaluate($object->getExcludeIf(), $values);
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
            $action->{'set'.$key}($value);
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

            $values = [
                'input' => $input,
                'objectSchema' => $objectSchema,
                'propertySchema' => $property,
            ];

            if ($this->shouldBeExcluded($input, $values)) {
                continue;
            }

            $inputPosition = 0;
            if (null !== $input->getPosition()) {
                $inputPosition = $input->getPosition();
            }

            $inputs[$inputPosition][] = $input;
        }

        if ($inputs) {
            ksort($inputs);
            $inputs = call_user_func_array('array_merge', $inputs);
        }

        $default = (new \ReflectionClass($objectSchema->getVendorData()['x-draw-dashboard-class-name']))->newInstance();

        return compact('inputs', 'default');
    }

    private function configureInput(FormInput $input, Schema $objectSchema, Schema $property, Root $openApiSchema)
    {
        if (!$input->getLabel()) {
            $input->setLabel($input->getId());
        }

        if ($input instanceof FormInputChoices && null === $input->getChoices()) {
            $input->setChoices($this->loadChoices($input, $objectSchema, $property, $openApiSchema));
            $input->setSourceCompareKeys(['id']); //todo make this dynamic
        }

        if ($input instanceof FormInputComposite && null === $input->getSubForm()) {
            $input->setSubForm($this->loadSubForm($input, $objectSchema, $property, $openApiSchema));
        }

        if ($input instanceof FormInputAutoComplete && null === $input->getRemoteUrl()) {
            $input->setRemoteUrl(
                $this->urlGenerator->generate(
                    $input->getRouteName(),
                    $input->getParameters(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
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

        if (null !== $property->items) {
            $target = $openApiSchema->resolveSchema($property->items);
        } else {
            $target = $openApiSchema->resolveSchema($property);
        }

        $values['managerRegistry'] = $this->managerRegistry;

        $targetClass = $target->getVendorData()['x-draw-dashboard-class-name'] ?? null;
        if ($targetClass && $manager = $this->managerRegistry->getManagerForClass($targetClass)) {
            $repository = $manager->getRepository($targetClass);
            $values['repository'] = $repository;
            if (!$expression) {
                $expression = 'repository.findAll()';
            }
        }

        $objects = $this->expressionLanguage->evaluate($expression, $values);

        $choices = [];
        foreach ($objects as $object) {
            $choices[] = [
                'label' => (string) $object,
                'value' => [
                    'id' => $object->getId(), //todo make this dynamic
                ],
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
