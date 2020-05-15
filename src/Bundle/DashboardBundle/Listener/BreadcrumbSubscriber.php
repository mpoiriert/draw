<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations\Breadcrumb;
use Draw\Bundle\DashboardBundle\Annotations\Translatable;
use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BreadcrumbSubscriber implements EventSubscriberInterface
{
    private $actionFinder;

    public static function getSubscribedEvents()
    {
        return [
            OptionBuilderEvent::class => ['buildBreadcrumb'],
        ];
    }

    public function __construct(
        ActionFinder $actionFinder
    ) {
        $this->actionFinder = $actionFinder;
    }

    public function buildBreadcrumb(OptionBuilderEvent $optionBuilderEvent): void
    {
        $action = $parentAction = $optionBuilderEvent->getAction();

        $breadcrumbs = [];
        $autoCreateParent = true;
        do {
            if (!($operation = $parentAction->getOperation())) {
                break;
            }
            $breadcrumb = $operation->getVendorData()['x-draw-dashboard-breadcrumb'] ?? null;
            if (!$breadcrumb instanceof Breadcrumb) {
                if(!$breadcrumbs || !$autoCreateParent) {
                    break;
                }
                $autoCreateParent = false;
                $breadcrumb = new Breadcrumb();
            }

            if(!$breadcrumb->getLabel()) {
                $breadcrumb->setLabel('_breadcrumb.' . $operation->operationId);
            }

            array_unshift(
                $breadcrumbs,
                [
                    'label' => $breadcrumb->getLabel(),
                    'href' => $parentAction->getHref() . '/' . $parentAction->getType()
                ]
            );

            if (!($parentOperationId = $breadcrumb->getParentOperationId())) {
                break;
            }
        } while ($parentAction = $this->actionFinder->findOneByOperationId($parentOperationId));


        if ($breadcrumbs && ($action->getTitle() === null)) {
            $action->setTitle($breadcrumbs[count($breadcrumbs) - 1]['label']);
        }

        array_unshift(
            $breadcrumbs,
            [
                'label' => new Translatable('_breadcrumb.home'),
                'href' => '/'
            ]
        );

        $optionBuilderEvent->getOptions()->set('x-draw-dashboard-breadcrumbs', $breadcrumbs);
    }
}