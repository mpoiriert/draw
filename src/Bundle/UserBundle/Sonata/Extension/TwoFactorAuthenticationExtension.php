<?php

namespace Draw\Bundle\UserBundle\Sonata\Extension;

use Draw\Bundle\UserBundle\Entity\TwoFactorAuthenticationTrait;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TwoFactorAuthenticationExtension extends AbstractAdminExtension
{
    /**
     * @var string
     */
    private $userAdminCode;

    public function __construct(string $userAdminCode)
    {
        $this->userAdminCode = $userAdminCode;
    }

    public function configureListFields(ListMapper $list)
    {
        if (!$this->needConfigure($list->getAdmin())) {
            return;
        }

        $list->add('isTotpAuthenticationEnabled', 'boolean', ['label' => '2FA enabled']);
    }

    public function configureFormFields(FormMapper $form)
    {
        if (!$this->needConfigure($form->getAdmin())) {
            return;
        }

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

    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        if (!$this->needConfigure($admin)) {
            return;
        }

        $collection->add('enable-2fa', $admin->getRouterIdParameter().'/enable-2fa');
        $collection->add('generate-2fa-qr-code', $admin->getRouterIdParameter().'/generate-2fa-qr-code');
        $collection->add('disable-2fa', $admin->getRouterIdParameter().'/disable-2fa');
    }

    /**
     * @param TwoFactorAuthenticationTrait $object
     */
    public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
    {
        if (!$object || !$this->needConfigure($admin)) {
            return $list;
        }

        if (!$object->getTotpSecret()) {
            $list['enable-2fa'] = [
                'template' => '@DrawUser/Sonata/Buttons/enable-2fa.html.twig',
            ];
        } else {
            $list['disable-2fa'] = [
                'template' => '@DrawUser/Sonata/Buttons/disable-2fa.html.twig',
            ];
        }

        return $list;
    }

    private function needConfigure(AdminInterface $admin): bool
    {
        if ($admin->getCode() !== $this->userAdminCode) {
            return false;
        }

        $subject = $admin->getSubject();
        if ($subject && !\in_array(TwoFactorAuthenticationTrait::class, class_uses($subject), true)) {
            return false;
        }

        return true;
    }
}
