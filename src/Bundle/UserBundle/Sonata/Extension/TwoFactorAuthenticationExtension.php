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
    public const FIELD_2FA_ENABLED = '2fa_enabled';

    private $fieldPositions;

    public function __construct(array $fieldPositions = [])
    {
        $this->fieldPositions = $fieldPositions;
    }

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
                'translation_domain' => 'DrawUserBundle'
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
                    'translation_domain' => 'DrawUserBundle'
                ]
            );

        if (false !== $index = array_search($before, $keys, true)) {
            array_splice($keys, $index, 0, 'totpAuthenticationEnabled');
            $form->reorder($keys);
        }
    }

    /**
     * @param TwoFactorAuthenticationUserInterface|object|null $object
     */
    public function backwardCompatibleConfigureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null
    ): array {
        if (!in_array($action, ['edit', 'show'])) {
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
