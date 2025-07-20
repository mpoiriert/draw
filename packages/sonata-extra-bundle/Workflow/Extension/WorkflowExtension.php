<?php

namespace Draw\Bundle\SonataExtraBundle\Workflow\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension\ActionableAdminExtensionInterface;
use Draw\Bundle\SonataExtraBundle\Workflow\Action\WorkflowTransitionAction;
use Draw\Bundle\SonataExtraBundle\Workflow\AdminAction\WorkflowTransitionAdminAction;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

class WorkflowExtension extends AbstractAdminExtension implements ActionableAdminExtensionInterface
{
    private Registry $registry;
    private array $options;

    public function __construct(Registry $registry, array $options = [])
    {
        $this->registry = $registry;
        $this->configureOptions($resolver = new OptionsResolver());
        $this->options = $resolver->resolve($options);
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'render_actions' => ['edit', 'show'],
                'workflow_name' => null,
                'no_transition_display' => false,
                'no_transition_label' => 'workflow_transitions_empty',
                'no_transition_icon' => 'fa fa-code-fork',
                'dropdown_transitions_label' => 'workflow_transitions',
                'dropdown_transitions_icon' => 'fa fa-code-fork',
                'transitions_default_icon' => null,
                'transitions_icons' => [],
                'view_transitions_role' => 'EDIT',
                'apply_transitions_role' => 'EDIT',
                'controller' => WorkflowTransitionAction::class,
                'admin_action_class' => WorkflowTransitionAdminAction::class,
                'ignore_transitions' => [],
            ])
            ->setAllowedTypes('render_actions', ['string[]'])
            ->setAllowedTypes('workflow_name', ['string', 'null'])
            ->setAllowedTypes('no_transition_display', ['bool'])
            ->setAllowedTypes('no_transition_label', ['string'])
            ->setAllowedTypes('no_transition_icon', ['string'])
            ->setAllowedTypes('dropdown_transitions_label', ['string'])
            ->setAllowedTypes('dropdown_transitions_icon', ['string', 'null'])
            ->setAllowedTypes('transitions_default_icon', ['string', 'null'])
            ->setAllowedTypes('transitions_icons', ['array'])
            ->setAllowedTypes('view_transitions_role', ['string'])
            ->setAllowedTypes('apply_transitions_role', ['string'])
            ->setAllowedTypes('controller', ['string'])
            ->setAllowedTypes('admin_action_class', ['string'])
            ->setAllowedTypes('ignore_transitions', ['array'])
        ;
    }

    #[\Override]
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        ?AdminInterface $childAdmin = null,
    ): void {
        if (null !== $childAdmin || !\in_array($action, $this->options['render_actions'], true)) {
            return;
        }

        $subject = $admin->getSubject();

        if (!$admin->hasAccess('viewTransitions', $subject)) {
            return;
        }

        $workflow = $this->registry->get($subject, $this->options['workflow_name']);

        $transitions = $workflow->getEnabledTransitions($subject);

        // Remove ignored transitions by their name
        $transitions = array_filter(
            $transitions,
            fn (Transition $transition) => !\in_array($transition->getName(), $this->options['ignore_transitions'], true),
        );

        if (0 === \count($transitions)) {
            $this->noTransitions($menu, $admin);
        } else {
            $this->transitionsDropdown($menu, $admin, $transitions, $subject);
        }
    }

    #[\Override]
    public function getAccessMapping(AdminInterface $admin): array
    {
        return [
            'viewTransitions' => $this->options['view_transitions_role'],
            'applyTransitions' => $this->options['apply_transitions_role'],
        ];
    }

    private function noTransitions(MenuItemInterface $menu, AdminInterface $admin): void
    {
        if (!$this->options['no_transition_display']) {
            return;
        }

        $menu->addChild(
            $this->options['no_transition_label'],
            [
                'uri' => '#',
                'attributes' => [
                    'icon' => $this->options['no_transition_icon'],
                ],
                'extras' => [
                    'translation_domain' => $admin->getTranslationDomain(),
                ],
            ]);
    }

    /**
     * @param iterable<Transition> $transitions
     */
    private function transitionsDropdown(
        MenuItemInterface $menu,
        AdminInterface $admin,
        iterable $transitions,
        object $subject,
    ): void {
        $workflowMenu = $menu->addChild(
            $this->options['dropdown_transitions_label'],
            [
                'attributes' => [
                    'dropdown' => true,
                    'icon' => $this->options['dropdown_transitions_icon'],
                ],
                'extras' => [
                    'translation_domain' => $admin->getTranslationDomain(),
                ],
            ]);

        foreach ($transitions as $transition) {
            $this->transitionsItem($workflowMenu, $admin, $transition, $subject);
        }
    }

    private function transitionsItem(
        MenuItemInterface $menu,
        AdminInterface $admin,
        Transition $transition,
        object $subject,
    ): void {
        $options = [
            'attributes' => [],
            'extras' => [
                'translation_domain' => $admin->getTranslationDomain(),
            ],
        ];

        if ($admin->hasAccess('applyTransitions', $subject)) {
            $options['uri'] = $admin->generateObjectUrl(
                'workflow_apply_transition',
                $subject,
                ['transition' => $transition->getName()]
            );
        }

        if ($icon = $this->getTransitionIcon($transition)) {
            $options['attributes']['icon'] = $icon;
        }

        $menu->addChild(
            $admin
                ->getLabelTranslatorStrategy()
                ->getLabel($transition->getName(), 'workflow', 'transition'),
            $options
        );
    }

    private function getTransitionIcon(Transition $transition): ?string
    {
        return $this->options['transitions_icons'][$transition->getName()]
            ?? $this->options['transitions_default_icon'];
    }

    public function getActions(AdminInterface $admin, array $actions): array
    {
        $actionClass = $this->options['admin_action_class'];

        $adminAction = new $actionClass();

        if (!$adminAction instanceof AdminAction) {
            throw new \LogicException(\sprintf('The action class "%s" must implement "%s".', $this->options['action_class'], AdminAction::class));
        }

        $adminAction->setController($this->options['controller']);

        return $actions + ['workflow_apply_transition' => $adminAction];
    }
}
