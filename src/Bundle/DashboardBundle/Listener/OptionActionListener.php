<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Annotations\ActionCreate;
use Draw\Bundle\DashboardBundle\Annotations\ConfirmFlow;
use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class OptionActionListener implements EventSubscriberInterface
{
    private $twig;

    public function __construct(Environment $environment)
    {
        $this->twig = $environment;
    }

    public static function getSubscribedEvents()
    {
        return [
            OptionBuilderEvent::class => [
                ['buildOption'],
                ['buildOptionForList'],
                ['buildOptionForCreate'],
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
        if($action->flow instanceof ConfirmFlow) {
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

    public function buildOptionForCreate(OptionBuilderEvent $event)
    {
        $action = $event->getAction();
        if ($action->getType() !== ActionCreate::TYPE) {
            return;
        }

        $operation = $event->getOperation();

        $bodyParameter = null;
        foreach($operation->parameters as $parameter) {
            if($parameter instanceof BodyParameter) {
                $bodyParameter = $parameter;
                break;
            }
        }

        if(!$bodyParameter) {
            return;
        }

        $openApiSchema = $event->getOpenApiSchema();
        $item = $openApiSchema->resolveSchema($bodyParameter->schema);

        $inputs = [];
        foreach ($item->properties as $property) {
            $input = $property->vendor['x-draw-form-input'] ?? null;
            if (!$input) {
                continue;
            }
            $inputs[] = $input;
        }

        $event->getOptions()->set('inputs', $inputs);
    }

    private function renderStringTemplate($template, array $context)
    {
        if(!$template) {
            return '';
        }

        return $this->twig->render(
            $this->twig->createTemplate($template),
            $context
        );
    }
}