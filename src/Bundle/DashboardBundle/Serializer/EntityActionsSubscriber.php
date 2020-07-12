<?php

namespace Draw\Bundle\DashboardBundle\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\DashboardBundle\Action\ActionBuilder;
use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityActionsSubscriber implements EventSubscriberInterface
{
    private $actionGroups = ['Default', 'DrawDashboard:Actions'];

    private $actionBuilder;

    private $actionFinder;

    private $urlGenerator;

    private $managerRegistry;

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'postSerialize',
                'format' => 'json',
            ],
        ];
    }

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $managerRegistry,
        ActionFinder $actionFinder,
        ActionBuilder $actionBuilder
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->managerRegistry = $managerRegistry;
        $this->actionFinder = $actionFinder;
        $this->actionBuilder = $actionBuilder;
    }

    public function postSerialize(ObjectEvent $objectEvent): void
    {
        $object = $objectEvent->getObject();
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $objectEvent->getVisitor();

        $canHaveAction = true;
        if ($manager = $this->managerRegistry->getManagerForClass(get_class($object))) {
            $canHaveAction = $manager->contains($object);
        }

        if (!$canHaveAction) {
            return;
        }

        $context = $objectEvent->getContext();

        // We test if we must scrip the property before the processing so we can save some time
        $propertyMetadata = new StaticPropertyMetadata('', '_actions', [], $this->actionGroups);
        if ($exclusionStrategy = $context->getExclusionStrategy()) {
            if ($exclusionStrategy->shouldSkipProperty($propertyMetadata, $context)) {
                return;
            }
        }

        $targetActions = $this->actionFinder->findAllByTarget($object, true);

        if (!$targetActions) {
            return;
        }

        $actions = $this->actionBuilder->buildActions($targetActions, $object);

        // Since there is no setter for the value we create a new property
        $propertyMetadata = new StaticPropertyMetadata('', '_actions', [], $this->actionGroups);

        // Pushing the property metadata replicate the flow and make sure any call to Context::getCurrentPath will work
        $context->pushPropertyMetadata($propertyMetadata);
        $visitor->visitProperty($propertyMetadata, $actions);
        $context->popPropertyMetadata();
    }
}
