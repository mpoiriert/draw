<?php

namespace Draw\Bundle\DashboardBundle\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Controller\OptionsController;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityActionsSubscriber implements EventSubscriberInterface
{
    private $actionGroups = ['Default', 'DrawDashboard:Actions'];

    private $actionFinder;

    private $urlGenerator;

    private $optionsController;

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
        OptionsController $optionsController,
        ManagerRegistry $managerRegistry,
        ActionFinder $actionFinder
    ) {
        $this->optionsController = $optionsController;
        $this->urlGenerator = $urlGenerator;
        $this->managerRegistry = $managerRegistry;
        $this->actionFinder = $actionFinder;
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

        $targetActions = $this->actionFinder->findAllByByTarget($object);

        if (!$targetActions) {
            return;
        }

        $actions = [];
        foreach ($targetActions as $action) {
            if (!$action->getIsInstanceTarget()) {
                continue;
            }

            $routeName = $action->getRouteName();
            $method = strtoupper($action->getMethod());
            $path = $this->urlGenerator->generate(
                $routeName,
                ['id' => $object->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            /** @var Response $response */
            list(, $response) = $this->optionsController->dummyHandling($method, $path);

            // We skip action that we do not have access
            if (403 === $response->getStatusCode()) {
                continue;
            }

            $action->setHref($path);
            $actions[] = $action;
        }

        // Since there is no setter for the value we create a new property
        $propertyMetadata = new StaticPropertyMetadata('', '_actions', [], $this->actionGroups);

        // Pushing the property metadata replicate the flow and make sure any call to Context::getCurrentPath will work
        $context->pushPropertyMetadata($propertyMetadata);
        $visitor->visitProperty($propertyMetadata, $actions);
        $context->popPropertyMetadata();
    }
}
