<?php namespace Draw\Bundle\DashboardBundle\Controller;

use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\Action;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private $actionFinder;

    private $optionsController;

    public function __construct(
        OptionsController $optionsController,
        ActionFinder $actionFinder
    ) {
        $this->actionFinder = $actionFinder;
        $this->optionsController = $optionsController;
    }

    /**
     * @Route(name="draw_dashboard", methods={"GET"}, path="/dashboard")
     */
    public function index(ParameterBagInterface $parameterBag, Request $request)
    {
        $menu = $parameterBag->get('draw_dashboard.menu');

        foreach ($menu as $index => &$menuEntry) {
            if ($menuEntry['security'] && !$this->isGranted(new Expression($menuEntry['security']))) {
                unset($menu[$index]);
                continue;
            }

            if ($menuEntry['operationId']) {
                $routeInformation = $this->getRouteInformation($menuEntry['operationId'], $request);
                if (!$routeInformation) {
                    unset($menu[$index]);
                    continue;
                } else {
                    unset($menuEntry['operationId']);
                    $menuEntry['link'] = $routeInformation;
                }
            }

            foreach ($menuEntry['children'] as $index2 => &$menuItem) {
                if ($menuItem['security'] && !$this->isGranted(new Expression($menuItem['security']))) {
                    unset($menuEntry['children'][$index2]);
                    continue;
                }

                if (!$menuItem['operationId']) {
                    continue;
                }

                $routeInformation = $this->getRouteInformation($menuItem['operationId'], $request);
                if (!$routeInformation) {
                    unset($menuEntry['children'][$index2]);
                    continue;
                }
                unset($menuItem['operationId']);
                $menuItem['link'] = $routeInformation;
            }

            $menuEntry['children'] = array_values($menuEntry['children']);
        }

        // Return value must be json array not a json object
        $menu = array_values($menu);

        $toolbar = $parameterBag->get('draw_dashboard.toolbar');

        foreach ($toolbar as $index => &$action) {
            $action = $this->getActionInformation($action['operationId'], $request);
            if (!$action) {
                unset($toolbar[$index]);
            }
        }

        // Return value must be json array not a json object
        $toolbar = array_values($toolbar);

        $title = $parameterBag->get('draw_dashboard.title');

        return compact('title', 'menu', 'toolbar');
    }

    private function getRouteInformation($operationId, Request $request): ?string
    {
        $action = $this->getActionInformation($operationId, $request);
        if ($action === null) {
            return null;
        }

        return $action->getHref() . '/' . $action->getType();
    }

    private function getActionInformation($operationId, Request $request): ?Action
    {
        $action = $this->actionFinder->findOneByOperationId($operationId);
        if ($action === null) {
            return null;
        }

        $routeInformation = $this->optionsController->loadOption(
            $action->getPath(),
            $request,
            [$action->getMethod()]
        );

        /** @var Action $action */
        switch (true) {
            case null === ($information = $routeInformation[$action->getMethod()] ?? null):
            case null === ($action = $information['x-draw-dashboard-action'] ?? null):
            case !$action instanceof Action:
            case $action->getAccessDenied():
                return null;
        }

        return $action;
    }
}