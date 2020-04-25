<?php namespace Draw\Bundle\DashboardBundle\Serializer;

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
                'format' => 'json'
            ]
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

        $isManaged = false;
        if ($manager = $this->managerRegistry->getManagerForClass(get_class($object))) {
            $isManaged = $manager->contains($object);
        }

        if (!$isManaged) {
            return;
        }

        $actions = [];

        foreach ($this->actionFinder->findAllByByTarget($object) as $action) {
            $routeName = $action->getRouteName();
            $method = strtoupper($action->getMethod());
            $path = $this->urlGenerator->generate(
                $routeName,
                ['id' => $object->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            /** @var Response $response */
            list(, $response) = $this->optionsController->dummyHandling($method, $path);

            if ($response->getStatusCode() === 403) {
                continue;
            }

            $action->setHref($path);
            $actions[] = $action;
        }

        $visitor->visitProperty(
            new StaticPropertyMetadata('', '_actions', $actions),
            $actions
        );
    }
}