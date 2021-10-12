<?php

namespace Draw\Bundle\UserBundle\Sonata\Extension;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthenticationUserInterface;
use Draw\Bundle\UserBundle\Sonata\Controller\TwoFactorAuthenticationController;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TwoFactorAuthenticationExtension extends AbstractAdminExtension
{
    protected function backwardCompatibleConfigureRoute(AdminInterface $admin, RouteCollectionInterface $collection)
    {
        $collection->add(
            'enable-2fa',
            $admin->getRouterIdParameter().'/enable-2fa',
            ['_controller' => TwoFactorAuthenticationController::class.':enable2faAction']
        );

        $collection->add(
            'disable-2fa',
            $admin->getRouterIdParameter().'/disable-2fa',
            ['_controller' => TwoFactorAuthenticationController::class.':disable2faAction']
        );
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
        $list->add('isTotpAuthenticationEnabled', 'boolean', ['label' => '2FA enabled']);
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->add(
                'isTotpAuthenticationEnabled',
                CheckboxType::class,
                [
                    'label' => '2FA enabled',
                    'disabled' => true,
                    'required' => false,
                ]
            );
    }

    /**
     * @param TwoFactorAuthenticationUserInterface|object|null $object
     */
    public function backwardCompatibleConfigureActionButtons(AdminInterface $admin, array $list, string $action, ?object $object = null): array
    {
        if (!$object) {
            return $list;
        }

        if (!$object->getTotpSecret() && $admin->hasAccess('enable-2fa', $object)) {
            $list['enable-2fa'] = [
                'template' => '@DrawUser/Sonata/Buttons/enable-2fa.html.twig',
            ];
        } elseif ($admin->hasAccess('disable-2fa', $object)) {
            $list['disable-2fa'] = [
                'template' => '@DrawUser/Sonata/Buttons/disable-2fa.html.twig',
            ];
        }

        return $list;
    }
}
