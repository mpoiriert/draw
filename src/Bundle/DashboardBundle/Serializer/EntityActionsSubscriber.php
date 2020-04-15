<?php namespace Draw\Bundle\DashboardBundle\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Controller\OptionsController;
use Draw\Bundle\OpenApiBundle\Controller\OpenApiController;
use Draw\Component\OpenApi\Schema\Root;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityActionsSubscriber implements EventSubscriberInterface
{
    private $openApiController;

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
        OpenApiController $openApiController,
        ManagerRegistry $managerRegistry
    ) {
        $this->optionsController = $optionsController;
        $this->urlGenerator = $urlGenerator;
        $this->openApiController = $openApiController;
        $this->managerRegistry = $managerRegistry;
    }

    public function postSerialize(ObjectEvent $objectEvent): void
    {
        $object = $objectEvent->getObject();
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $objectEvent->getVisitor();

        $isManaged = false;
        if($manager = $this->managerRegistry->getManagerForClass(get_class($object))) {
            $isManaged = $manager->contains($object);
        }

        if(!$isManaged) {
            return;
        }

        $actionsInfo = $this->getActions($object, $this->openApiController->loadOpenApiSchema());
        $links = [];

        foreach ($actionsInfo as $actionInfo) {
            /** @var Action $action */
            list($method, $routeName, $action, $operation) = $actionInfo;

            $path = $this->urlGenerator->generate(
                $routeName,
                ['id' => $object->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            /** @var Response $response */
            list(,$response) = $this->optionsController->dummyHandling($method, $path);

            if($response->getStatusCode() === 403) {
                continue;
            }

            $links[] = [
                'rel' => 'self',
                'href' => $path,
                'method' => strtoupper($method),
                'x-draw-action' => $action->jsonSerialize()
            ];
        }

        if ($links) {
            $visitor->visitProperty(
                new StaticPropertyMetadata('', '_links', $links),
                $links
            );
        }
    }

    /**
     * @param $object
     * @param Root $rootSchema
     * @return array
     */
    private function getActions($object, Root $rootSchema): iterable
    {
        foreach ($rootSchema->paths as $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                $routeName = $operation->getVendorData()['x-symfony-route'] ?? null;
                if (is_null($routeName)) {
                    continue;
                }

                /** @var Action $action */
                $action = $operation->getVendorData()['x-draw-action'] ?? null;
                if (is_null($action) || !$action instanceof Action) {
                    continue;
                }

                foreach ($action->targets as $target) {
                    if (!$object instanceof $target) {
                        continue;
                    }

                    yield [$method, $routeName, $action, $operation];
                    break;
                }
            }
        }
    }
}