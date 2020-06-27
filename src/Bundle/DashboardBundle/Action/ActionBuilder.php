<?php

namespace Draw\Bundle\DashboardBundle\Action;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Controller\OptionsController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActionBuilder
{
    private $urlGenerator;
    private $optionsController;

    public function __construct(UrlGeneratorInterface $urlGenerator, OptionsController $optionsController)
    {
        $this->urlGenerator = $urlGenerator;
        $this->optionsController = $optionsController;
    }

    /**
     * @param array|Action[] $actions
     * @param null $object
     *
     * @return array|Action[]
     */
    public function buildActions(array $actions, $object = null): array
    {
        $result = [];
        foreach ($actions as $action) {
            $target = null;
            if ($action->getIsInstanceTarget()) {
                if (!$this->isValidTarget($action, $object)) {
                    continue;
                }

                $target = $object;
            } elseif ($object) {
                continue;
            }

            $routeName = $action->getRouteName();
            $method = strtoupper($action->getMethod());
            $path = $this->urlGenerator->generate(
                $routeName,
                $target ? ['id' => $target->getId()] : [], // todo make this dynamic
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            /** @var Response $response */
            list(, $response) = $this->optionsController->dummyHandling($method, $path);

            // We skip action that we do not have access
            if (403 === $response->getStatusCode()) {
                continue;
            }

            $action->setHref($path);
            $result[] = $action;
        }

        return $result;
    }

    private function isValidTarget(Action $action, $object)
    {
        if (!$object) {
            return false;
        }

        foreach ($action->getTargets() as $targetClass) {
            if ($object instanceof $targetClass) {
                return true;
            }
        }

        return false;
    }
}
