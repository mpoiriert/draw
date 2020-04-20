<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\DashboardBundle\Annotations\ActionCreate;
use Draw\Bundle\DashboardBundle\Annotations\ActionEdit;
use Draw\Bundle\DashboardBundle\Annotations\ConfirmFlow;
use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class OptionActionListener implements EventSubscriberInterface
{
    private $twig;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    private $serializer;

    public function __construct(
        Environment $environment,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer
    ) {
        $this->twig = $environment;
        $this->managerRegistry = $managerRegistry;
        $this->serializer = $serializer;
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
        $options = $event->getOptions();

        if (empty($action->flow)) {
            return;
        }

        $request = $event->getRequest();
        if ($action->flow instanceof ConfirmFlow) {
            $action->flow->message = $this->renderStringTemplate(
                $action->flow->message,
                $request->attributes->all()
            );

            $action->flow->title = $this->renderStringTemplate(
                $action->flow->title,
                $request->attributes->all()
            );
        }

        $options->set('flow', $action->flow);
    }

    public function buildOptionForList(OptionBuilderEvent $event)
    {
        $action = $event->getAction();
        if ($action->getType() !== 'list') {
            return;
        }

        $operation = $event->getOperation();

        switch (true) {
            case !isset($operation->responses[200]):
            case is_null($responseSchema = $operation->responses[200]->schema):
            case !isset($responseSchema->properties['data']):
                return;
        }

        $openApiSchema = $event->getOpenApiSchema();
        $item = $responseSchema->properties['data']->items;
        $item = $openApiSchema->resolveSchema($item);

        $columns = [];
        foreach ($item->properties as $property) {
            $column = $property->vendor['x-draw-column'] ?? null;
            if (!$column) {
                continue;
            }
            $columns[] = $column;
        }

        $columns[] = [
            'id' => '_actions',
            'type' => 'actions',
            'label' => 'Actions'
        ];

        $event->getOptions()->set('columns', $columns);
    }

    public function buildOptionForCreateEdit(OptionBuilderEvent $event)
    {
        $action = $event->getAction();
        if (!in_array($action->getType(), [ActionCreate::TYPE, ActionEdit::TYPE])) {
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

        $options = $event->getOptions();

        foreach($this->loadFormInputs($item, $openApiSchema) as $key => $value) {
            $options->set($key, $value);
        }
    }

    private function loadFormInputs(Schema $objectSchema, Root $openApiSchema)
    {
        $objectSchema = $openApiSchema->resolveSchema($objectSchema);
        $inputs = [];
        foreach ($objectSchema->properties as $property) {
            $input = $property->vendor['x-draw-form-input'] ?? null;
            if (!$input) {
                continue;
            }

            if ($input['type'] === 'choices' && empty($input['choices'])) {
                $input['choices'] = $this->loadChoices($input, $objectSchema, $property, $openApiSchema);
                $input['sourceCompareKeys'] = ['id']; //todo make this dynamic
            }

            if($input['type'] === 'composite') {
                $input['subForm'] = $this->loadSubForm($input, $objectSchema, $property, $openApiSchema);
            }

            if($input['type'] === 'collection') {
                $input['subForm'] = $this->loadSubForm(
                    $input,
                    $objectSchema,
                    $openApiSchema->resolveSchema($property->items),
                    $openApiSchema
                );
            }

            $inputs[] = $input;
        }

        $object = (new \ReflectionClass($objectSchema->getVendorData()['x-draw-dashboard-class-name']))->newInstance();
        $default = json_decode($this->serializer->serialize($object, 'json'));
        return compact('inputs', 'default');
    }

    private function loadSubForm(array $input, Schema $schema, Schema $property, Root $openApiSchema)
    {
        return $this->loadFormInputs($property, $openApiSchema);
    }

    private function loadChoices(array $input, Schema $schema, Schema $property, Root $openApiSchema)
    {
        $target = $openApiSchema->resolveSchema($property->items);
        $targetClass = $target->getVendorData()['x-draw-dashboard-class-name'];
        $objects = $this->managerRegistry
            ->getManagerForClass($targetClass)
            ->getRepository($targetClass)
            ->{$input['repositoryMethod'] ?? 'findAll'}();

        $choices = [];
        foreach ($objects as $object) {
            $choices[(string)$object] = ['id' => $object->getId()];//todo make this dynamic
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