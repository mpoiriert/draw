<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataIntegrationBundle\User\Form\AdminLoginForm;
use Draw\Bundle\SonataIntegrationBundle\User\Form\ChangePasswordForm;
use Draw\Bundle\SonataIntegrationBundle\User\Form\ForgotPasswordForm;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @Route("/resetting/forgot-password", name="admin_forgot_password", methods={"GET", "POST"})
     */
    public function forgotPasswordAction(
        Request $request,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(
            ForgotPasswordForm::class,
            ['email' => $this->authenticationUtils->getLastUsername()]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mailer->send(new ForgotPasswordEmail($form->get('email')->getData()));

            return new RedirectResponse($this->generateUrl('admin_check_email'));
        }

        return $this->render(
            '@DrawUser/security/forgot_password.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/resetting/check-email", name="admin_check_email")
     */
    public function checkEmailAction(): Response
    {
        return $this->render('@DrawUser/security/check_email.html.twig');
    }

    /**
     * @Route("/confirmation", name="draw_user_account_confirmation")
     */
    public function confirmationAction(): Response
    {
        return $this->render('@DrawUser/security/confirmation.html.twig');
    }

    /**
     * @Route("/login", name="admin_login")
     */
    public function loginAction(): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        $form = $this->createForm(
            AdminLoginForm::class,
            ['email' => $this->authenticationUtils->getLastUsername()]
        );

        return $this->render(
            '@DrawUser/security/login.html.twig',
            [
                'last_username' => $this->authenticationUtils->getLastUsername(),
                'form' => $form->createView(),
                'error' => $this->authenticationUtils->getLastAuthenticationError(),
            ]
        );
    }

    /**
     * @Route("/logout", name="admin_logout")
     */
    public function logoutAction(): void
    {
        // Left empty intentionally because this will be handled by Symfony.
    }

    /**
     * @Route("/change-password", name="admin_change_password")
     */
    public function changePasswordAction(
        Request $request,
        UserFeedInterface $userFeed,
        ManagerRegistry $managerRegistry
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(
            ChangePasswordForm::class,
            $user
        );

        $form->handleRequest($request);

        switch (true) {
            case $form->isSubmitted():
            case !$request->query->has('t'):
            case !$user instanceof SecurityUserInterface:
            case null === $passwordUpdatedAt = $user->getPasswordUpdatedAt():
            case $passwordUpdatedAt->getTimestamp() <= $request->query->getInt('t'):
                break;
            default:
                return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManagerForClass(get_class($user))->flush();
            $userFeed->addToFeed($user, 'success', 'password_changed');

            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }

        return $this->render(
            '@DrawUser/security/reset.html.twig',
            ['form' => $form->createView()]
        );
    }
}
