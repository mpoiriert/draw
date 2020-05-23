<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Annotations\ActionEdit;
use Draw\Bundle\DashboardBundle\Annotations\Button\Button;
use Draw\Bundle\DashboardBundle\Annotations\FlowWithButtonsInterface;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\CloseDialog;
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
                ['then', 31],
                ['saveNotification', 31],
                ['closeDialog', 31],
            ],
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

    public function then(ViewEvent $viewEvent): void
    {
        switch (true) {
            case !($controllerResult = $viewEvent->getControllerResult()):
            case null === ($button = $this->getButtonToProcess($viewEvent->getRequest())):
            case !($thenActionName = $this->getThenActionName($button)):
                return;
        }

        foreach ($this->actionFinder->findAllByByTarget($viewEvent->getControllerResult()) as $action) {
            if($action->getName() !== $thenActionName) {
                continue;
            }

            $parameters = [];
            if($action->getIsInstanceTarget()) {
                $parameters['id'] = $controllerResult->getId(); // todo Make this dynamic
            }

            $url = $this->urlGenerator->generate(
                $action->getRouteName(),
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $action->setHref($url);
            $this->feedbackNotifier->sendFeedback(new Navigate($action));
            break;
        }
    }

    private function getThenActionName(Button $button): ?string
    {
        foreach($button->getBehaviours() as $behaviour) {
            if(strpos($behaviour, 'then-') !== 0) {
                continue;
            }

            return substr($behaviour, strlen('then-'));
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

        $template = $action->getTemplate(
            'notification_save',
            "{{ '_notification.save'|trans({'%entry%': request.get('object')}, 'DrawDashboardBundle')|raw }}"
        );

        if (!$template) {
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
            $flow = null;
            $buttonToProcess = null;
            switch (true) {
                case !($buttonId = $request->headers->get('X-Draw-Dashboard-Button-Id')):
                case null === ($action = $this->getActionToProcess($request)):
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