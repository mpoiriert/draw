<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\ActionEdit;
use Draw\Bundle\DashboardBundle\Annotations\Button;
use Draw\Bundle\DashboardBundle\Annotations\Flow;
use Draw\Bundle\DashboardBundle\Annotations\FlowWithButtonsInterface;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\Navigate;
use Draw\Bundle\DashboardBundle\Feedback\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResponseButtonBehaviourSubscriber implements EventSubscriberInterface
{
    private $actionFinder;

    private $feedbackNotifier;

    private $urlGenerator;

    public static function getSubscribedEvents()
    {
        // Must be executed before DrawOpenApiBundle's listener
        return [
            ViewEvent::class => [
                ['thenEdit', 31],
                ['saveNotification', 31],
            ]
        ];
    }

    public function __construct(
        ActionFinder $actionFinder,
        UrlGeneratorInterface $urlGenerator,
        FeedbackNotifier $feedbackNotifier
    ) {
        $this->actionFinder = $actionFinder;
        $this->feedbackNotifier = $feedbackNotifier;
        $this->urlGenerator = $urlGenerator;
    }

    public function thenEdit(ViewEvent $viewEvent): void
    {
        switch (true) {
            case !($controllerResult = $viewEvent->getControllerResult()):
            case is_null($button = $this->getButtonToProcess($viewEvent->getRequest())):
            case !in_array('then-edit', $button->getBehaviours()):
                return;
        }

        foreach ($this->actionFinder->findAllByByTarget($viewEvent->getControllerResult()) as $action) {
            if ($action instanceof ActionEdit) {
                $url = $this->urlGenerator->generate(
                    $action->getRouteName(),
                    ['id' => $controllerResult->getId()], // todo Make this dynamic
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $action->setHref($url);

                $this->feedbackNotifier->sendFeedback(new Navigate($action));
            }
        }
    }

    public function saveNotification(ViewEvent $viewEvent)
    {
        switch (true) {
            case !($controllerResult = $viewEvent->getControllerResult()):
            case is_null($button = $this->getButtonToProcess($viewEvent->getRequest())):
            case !in_array('save', $button->getBehaviours()):
                return;
        }

        $this->feedbackNotifier->sendFeedback(
            new Notification(
                Notification::TYPE_SUCCESS,
                'The entry have been saved properly'
            )
        );
    }

    private function getButtonToProcess(
        Request $request
    ): ?Button {

        switch (true) {
            case !($buttonId = $request->headers->get('X-Draw-Dashboard-Button-Id')):
            case !($route = $request->attributes->get('_route')):
            case is_null($action = $this->actionFinder->findOneByRoute($route)):
            case is_null($flow = $action->getFlow()):
                return null;
        }

        if (!$flow instanceof FlowWithButtonsInterface) {
            return null;
        }

        $buttonToProcess = null;
        foreach ($flow->getButtons() as $button) {
            if ($button->getId() === $buttonId) {
                return $button;
            }
        }

        return null;
    }
}