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
        $action = $optionBuilderEvent->getAction();

        $breadcrumbs = [];
        do {
            if (!($operation = $action->getOperation())) {
                break;
            }
            $breadcrumb = $operation->getVendorData()['x-draw-dashboard-breadcrumb'] ?? null;
            if (!$breadcrumb instanceof Breadcrumb) {
                break;
            }

            array_unshift(
                $breadcrumbs,
                [
                    'label' => $label = $breadcrumb->getLabel(),
                    'href' => $action->getHref() . '/' . $action->getType()
                ]
            );

            if (!($parentOperationId = $breadcrumb->getParentOperationId())) {
                break;
            }


        } while ($action = $this->actionFinder->findOneByOperationId($parentOperationId));


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