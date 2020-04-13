<?php namespace App\Controller\Api;

use Draw\Bundle\DashboardBundle\Controller\OptionsController;
use Draw\Bundle\OpenApiBundle\Controller\OpenApiController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardController extends AbstractController
{
    private $openApiController;

    private $optionsController;

    public function __construct(OpenApiController $openApiController, OptionsController $optionsController)
    {
        $this->openApiController = $openApiController;
        $this->optionsController = $optionsController;
    }

    /**
     * @Route(methods={"GET"}, path="")
     */
    public function index(ParameterBagInterface $parameterBag, Request $request)
    {
        $menu = $parameterBag->get('draw_dashboard.menu');

        foreach ($menu as $index => &$menuEntry) {
            if ($menuEntry['operationId']) {
                $routeInformation = $this->getRouteInformation($menuEntry['operationId'], $request);
                if (!$routeInformation) {
                    unset($menu[$index]);
                } else {
                    unset($menuEntry['operationId']);
                    $menuEntry['link'] = $routeInformation;
                }
            }

            foreach ($menuEntry['children'] as $index2 => &$menuItem) {
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
        }

        // Return value must be json array not a json object
        $menu = array_values($menu);

        $toolbar = $parameterBag->get('draw_dashboard.toolbar');

        foreach ($toolbar as $index => &$action) {
            $actionInformation = $this->getActionInformation($action['operationId'], $request);
            if (!$actionInformation) {
                unset($toolbar[$index]);
            } else {
                unset($action['operationId']);
                $action = $actionInformation;
                $action = array_merge($action, $action['x-draw-action']);
                unset($action['x-draw-action']);
            }
        }

        // Return value must be json array not a json object
        $toolbar = array_values($toolbar);

        return compact('menu', 'toolbar');
    }

    private function getRouteInformation($operationId, Request $request)
    {
        $action = $this->getActionInformation($operationId, $request);
        if (is_null($action)) {
            return null;
        }

        return $action['href'] . '/' . $action['x-draw-action']['type'];
    }

    private function getActionInformation($operationId, Request $request)
    {
        $schema = $this->openApiController->loadOpenApiSchema();
        foreach ($schema->paths as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                if ($operation->operationId === $operationId) {
                    $routeInformation = $this->optionsController->loadOption(
                        $path,
                        $request
                    );

                    $information = $routeInformation[strtoupper($method)] ?? null;
                    if (!$information) {
                        return null;
                    }

                    if ($information['x-draw-action']['accessDenied'] ?? false) {
                        return null;
                    }

                    return [
                        'href' => trim($this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL), '/') . $path,
                        'method' => strtoupper($method),
                        'x-draw-action' => $information['x-draw-action']
                    ];
                }
            }
        }
    }
}