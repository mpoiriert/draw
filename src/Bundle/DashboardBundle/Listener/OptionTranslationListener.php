<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Flow\JSONPath\JSONPath;
use JsonPath\JsonObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OptionTranslationListener implements EventSubscriberInterface
{
    private $translations = [
        [
            'path' => '$..columns[*]',
            'property' => 'label',
        ],
        [
            'path' => '$..inputs[*]',
            'property' => 'label',
        ],
    ];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public static function getSubscribedEvents()
    {
        return [
            OptionBuilderEvent::class => [
                ['translationOption', -32],
            ]
        ];
    }

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function translationOption(OptionBuilderEvent $event)
    {
        $options = $event->getOptions()->all();
        $jsonObject = new JsonObject($options);

        foreach($this->translations as $configuration) {
            $elements = $jsonObject->getJsonObjects($configuration['path']);
            if($elements) {
                /** @var JsonObject $element */
                foreach($elements as $element) {
                    $element->set('$.' . $configuration['property'], $this->translator
                        ->trans(
                            $element->get('$.' . $configuration['property'])[0],
                            [],
                            $configuration['domain'] ?? 'dashboard'
                        )
                    );
                }
            }
        }

        $event->getOptions()->replace($jsonObject->getValue());
    }
}