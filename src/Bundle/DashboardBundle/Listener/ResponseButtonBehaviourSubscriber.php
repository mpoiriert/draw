<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Annotations\ActionEdit;
use Draw\Bundle\DashboardBundle\Annotations\Button;
use Draw\Bundle\DashboardBundle\Annotations\FlowWithButtonsInterface;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\Navigate;
use Draw\Bundle\DashboardBundle\Feedback\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ResponseButtonBehaviourSubscriber implements EventSubscriberInterface
{
    private $actionFinder;

    private $feedbackNotifier;

    private $urlGenerator;

    private $twig;

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
        FeedbackNotifier $feedbackNotifier,
        Environment $twig
    ) {
        $this->actionFinder = $actionFinder;
        $this->feedbackNotifier = $feedbackNotifier;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
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
        $request = $viewEvent->getRequest();
        $controllerResult = $viewEvent->getControllerResult();

        switch (true) {
            case is_null($button = $this->getButtonToProcess($request)):
            case !in_array('save', $button->getBehaviours()):
            case is_null($action = $this->getActionToProcess($request)):
                return;
        }

        $template = $action->getTemplate(
            'notification_save',
            "{{ 'notification.save'|trans({'%entry%': request.get('object')}, 'DrawDashboardBundle')|raw }}"
        );

        if(!$template) {
            return;
        }

        $this->feedbackNotifier->sendFeedback(
            new Notification(
                Notification::TYPE_SUCCESS,
                $this->twig->render(
                    $this->twig->createTemplate($template),
                    ['request' => $request]
                )
            )
        );
    }

    private function getButtonToProcess(Request $request): ?Button
    {
        if (!$request->attributes->has('_draw_dashboard_button')) {
            $flow = null;
            $buttonToProcess = null;
            switch (true) {
                case !($buttonId = $request->headers->get('X-Draw-Dashboard-Button-Id')):
                case is_null($action = $this->getActionToProcess($request)):
                case ($flow = $action->getFlow()):
                    break;
            }

            if ($flow instanceof FlowWithButtonsInterface) {
                foreach ($flow->getButtons() as $button) {
                    if ($button->getId() === $buttonId) {
                        $buttonToProcess = $button;
                        break;
                    }
                }
            }

            $request->attributes->set('_draw_dashboard_button', $buttonToProcess);
        }

        return $request->attributes->get('_draw_dashboard_button');
    }

    private function getActionToProcess(Request $request): ?Action
    {
        if (!$request->attributes->has('_draw_dashboard_action')) {
            $action = null;
            switch (true) {
                case !($route = $request->attributes->get('_route')):
                case ($action = $this->actionFinder->findOneByRoute($route)):
                    break;
            }

            $request->attributes->set('_draw_dashboard_action', $action);
        }

        return $request->attributes->get('_draw_dashboard_action');
    }
}