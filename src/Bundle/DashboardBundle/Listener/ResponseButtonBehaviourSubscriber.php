<?php

namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Action\ActionBuilder;
use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Annotations\Button\Button;
use Draw\Bundle\DashboardBundle\Annotations\FlowWithButtonsInterface;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\CloseDialog;
use Draw\Bundle\DashboardBundle\Feedback\EntityDeleted;
use Draw\Bundle\DashboardBundle\Feedback\Navigate;
use Draw\Bundle\DashboardBundle\Feedback\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ResponseButtonBehaviourSubscriber implements EventSubscriberInterface
{
    private $actionBuilder;

    private $actionFinder;

    private $feedbackNotifier;

    private $urlGenerator;

    private $twig;

    public static function getSubscribedEvents()
    {
        // Must be executed before DrawOpenApiBundle's listener
        return [
            ViewEvent::class => [
                ['then', 31],
                ['saveNotification', 31],
                ['deleteNotification', 31],
                ['closeDialog', 31],
                ['navigateTo', 31],
            ],
        ];
    }

    public function __construct(
        ActionFinder $actionFinder,
        ActionBuilder $actionBuilder,
        UrlGeneratorInterface $urlGenerator,
        FeedbackNotifier $feedbackNotifier,
        Environment $twig
    ) {
        $this->actionFinder = $actionFinder;
        $this->actionBuilder = $actionBuilder;
        $this->feedbackNotifier = $feedbackNotifier;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function then(ViewEvent $viewEvent): void
    {
        switch (true) {
            case !($controllerResult = $viewEvent->getControllerResult()):
            case null === ($button = $this->getButtonToProcess($viewEvent->getRequest())):
            case !($thenActionName = $this->getPrefixedBehaviour($button, 'then-')):
                return;
        }

        foreach ($this->actionFinder->findAllByTarget($controllerResult) as $action) {
            if ($action->getName() !== $thenActionName) {
                continue;
            }

            $action = $this->actionBuilder->buildActions([$action], $controllerResult)[0] ?? null;

            if ($action) {
                $this->feedbackNotifier->sendFeedback(new Navigate($action));
            }
            break;
        }
    }

    private function getPrefixedBehaviour(Button $button, $prefix): ?string
    {
        foreach ($button->getBehaviours() as $behaviour) {
            if (0 !== strpos($behaviour, $prefix)) {
                continue;
            }

            return substr($behaviour, strlen($prefix));
        }

        return null;
    }

    public function saveNotification(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        switch (true) {
            case null === ($button = $this->getButtonToProcess($request)):
            case !in_array('save', $button->getBehaviours()):
            case null === ($action = $this->getActionToProcess($request)):
                return;
        }

        $this->sendNotification(
            $request,
            $action,
            'save',
            Notification::TYPE_SUCCESS
        );
    }

    public function deleteNotification(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        switch (true) {
            case null === ($button = $this->getButtonToProcess($request)):
            case !in_array('delete', $button->getBehaviours()):
            case null === ($action = $this->getActionToProcess($request)):
                return;
        }

        $this->feedbackNotifier->sendFeedback(new EntityDeleted());

        $this->sendNotification(
            $request,
            $action,
            'delete',
            Notification::TYPE_SUCCESS
        );
    }

    private function sendNotification(Request $request, Action $action, $name, $type)
    {
        $requestAttribute = $action->getRequestAttributeName();

        $template = $action->getTemplate(
            'notification_'.$name,
            "{{ '_notification.".$name."'|trans({'%entry%': request.attributes.get('$requestAttribute')}, 'DrawDashboardBundle')|raw }}"
        );

        if (!$template) {
            return;
        }

        $this->feedbackNotifier->sendFeedback(
            new Notification(
                $type,
                $this->twig->render(
                    $this->twig->createTemplate($template),
                    ['request' => $request]
                )
            )
        );
    }

    public function closeDialog(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        if (!($dialogId = $request->headers->get('X-Draw-Dashboard-Dialog-Id'))) {
            return;
        }

        $this->feedbackNotifier->sendFeedback(new CloseDialog($dialogId));
    }

    private function getButtonToProcess(Request $request): ?Button
    {
        if (!$request->attributes->has('_draw_dashboard_button')) {
            $action = null;
            $flow = null;
            $buttonToProcess = null;
            switch (true) {
                case !($buttonId = $request->headers->get('X-Draw-Dashboard-Button-Id')):
                case null === ($action = $this->getActionToProcess($request)):
                case $flow = $action->getFlow():
                    break;
            }

            $buttons = [];
            if ($action instanceof Action && $action->getButton()) {
                $buttons[] = $action->getButton();
            }

            if ($flow instanceof FlowWithButtonsInterface) {
                $buttons = array_merge($buttons, $flow->getButtons());
            }

            foreach ($buttons as $button) {
                if ($button->getId() === $buttonId) {
                    $buttonToProcess = $button;
                    break;
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
                case $action = $this->actionFinder->findOneByRoute($route):
                    break;
            }

            $request->attributes->set('_draw_dashboard_action', $action);
        }

        return $request->attributes->get('_draw_dashboard_action');
    }

    public function navigateTo(ViewEvent $viewEvent): void
    {
        switch (true) {
            case null === ($button = $this->getButtonToProcess($viewEvent->getRequest())):
            case !($navigateToOperationId = $this->getPrefixedBehaviour($button, 'navigateTo-')):
            case null === ($action = $this->actionFinder->findOneByOperationId($navigateToOperationId)):
            case null === ($action = $this->actionBuilder->buildActions([$action], $viewEvent->getControllerResult())[0] ?? null):
                return;
        }

        $this->feedbackNotifier->sendFeedback(new Navigate($action));
    }
}
