<?php

namespace Draw\Bundle\SonataExtraBundle\Listener;

use Draw\Bundle\SonataExtraBundle\Event\FormContractorDefaultOptionsEvent;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoHelpSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield FormContractorDefaultOptionsEvent::class => 'configureHelp';
    }

    public function configureHelp(FormContractorDefaultOptionsEvent $event): void
    {
        $fieldDescription = $event->getFieldDescription();

        $class = $fieldDescription->getAdmin()->getClass();

        if (!$class) {
            return;
        }

        $defaultOptions = $event->getDefaultOptions();

        if ($help = $this->extractHelp($class, $fieldDescription->getName())) {
            $defaultOptions['help'] = $help;
        }

        $event->setDefaultOptions($defaultOptions);
    }

    private function extractHelp(string $class, string $propertyName): string
    {
        $mainReflectionClass = $reflectionClass = new ReflectionClass($class);

        do {
            if ($reflectionClass->hasProperty($propertyName)) {
                $property = $reflectionClass->getProperty($propertyName);
                $docBlock = DocBlockFactory::createInstance()->create($property->getDocComment());

                return $docBlock->getSummary();
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        if ($mainReflectionClass->hasMethod('getTranslationEntityClass')) {
            return $this->extractHelp(
                $mainReflectionClass->getMethod('getTranslationEntityClass')->invoke(null),
                $propertyName
            );
        }

        return '';
    }
}
