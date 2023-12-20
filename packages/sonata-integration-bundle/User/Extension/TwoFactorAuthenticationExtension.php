<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Extension;

use Draw\Bundle\SonataIntegrationBundle\User\Controller\TwoFactorAuthenticationController;
use Draw\Component\Security\Core\Security;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TwoFactorAuthenticationExtension extends AbstractAdminExtension
{
    final public const FIELD_2FA_ENABLED = '2fa_enabled';

    public function __construct(private array $fieldPositions, private Security $security)
    {
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        return [
            'enable-2fa' => ['EDIT'],
            'disable-2fa' => ['EDIT'],
        ];
    }

    public function configureListFields(ListMapper $list): void
    {
        if (!isset($this->fieldPositions[static::FIELD_2FA_ENABLED]['list'])) {
            return;
        }

        if ($list->has('totpAuthenticationEnabled')) {
            return;
        }

        $before = $this->fieldPositions[static::FIELD_2FA_ENABLED]['list'];
        $keys = $list->keys();
        $list->add(
            'totpAuthenticationEnabled',
            'boolean',
            [
                'label' => 'admin.list.2fa_enabled',
                'translation_domain' => 'DrawUserBundle',
            ]
        );
        if (false !== $index = array_search($before, $keys, true)) {
            array_splice($keys, $index, 0, 'totpAuthenticationEnabled');
            $list->reorder($keys);
        }
    }

    public function configureFormFields(FormMapper $form): void
    {
        if (!$form->getAdmin()->id($form->getAdmin()->getSubject())) {
            return;
        }

        if (!isset($this->fieldPositions[static::FIELD_2FA_ENABLED]['form'])) {
            return;
        }

        if ($form->has('totpAuthenticationEnabled')) {
            return;
        }

        $before = $this->fieldPositions[static::FIELD_2FA_ENABLED]['form'];
        $keys = $form->keys();
        $form
            ->add(
                'totpAuthenticationEnabled',
                CheckboxType::class,
                [
                    'label' => 'admin.form.2fa_enabled',
                    'disabled' => true,
                    'required' => false,
                ],
                [
                    'translation_domain' => 'DrawUserBundle',
                ]
            );

        if (false !== $index = array_search($before, $keys, true)) {
            array_splice($keys, $index, 0, 'totpAuthenticationEnabled');
            $form->reorder($keys);
        }
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        $collection->add(
            'enable-2fa',
            $admin->getRouterIdParameter().'/enable-2fa',
            ['_controller' => TwoFactorAuthenticationController::class.'::enable2faAction']
        );

        $collection->add(
            'disable-2fa',
            $admin->getRouterIdParameter().'/disable-2fa',
            ['_controller' => TwoFactorAuthenticationController::class.'::disable2faAction']
        );
    }

    public function configureActionButtons(AdminInterface $admin, $list, $action, ?object $object = null): array
    {
        if (!\in_array($action, ['edit', 'show'])) {
            return $list;
        }

        switch (true) {
            case $object->getTotpSecret():
            case !$admin->hasAccess('enable-2fa', $object):
            case $this->security->getUser() !== $object:
                break;
            default:
                $list['enable-2fa'] = [
                    'template' => '@DrawSonataIntegration/User/Buttons/enable-2fa.html.twig',
                ];
                break;
        }

        switch (true) {
            case !$object->getTotpSecret():
            case !$admin->hasAccess('disable-2fa', $object):
                break;
            default:
                $list['disable-2fa'] = [
                    'template' => '@DrawSonataIntegration/User/Buttons/disable-2fa.html.twig',
                ];
                break;
        }

        return $list;
    }
}
