<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataIntegrationBundle\User\Form\AdminLoginForm;
use Draw\Bundle\SonataIntegrationBundle\User\Form\ChangePasswordForm;
use Draw\Bundle\SonataIntegrationBundle\User\Form\ForgotPasswordForm;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
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
    public function __construct(private AuthenticationUtils $authenticationUtils)
    {
    }

    #[Route(path: '/resetting/forgot-password', name: 'admin_forgot_password', methods: ['GET', 'POST'])]
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

    #[Route(path: '/resetting/check-email', name: 'admin_check_email')]
    public function checkEmailAction(): Response
    {
        return $this->render('@DrawUser/security/check_email.html.twig');
    }

    #[Route(path: '/confirmation', name: 'draw_user_account_confirmation')]
    public function confirmationAction(): Response
    {
        return $this->render('@DrawUser/security/confirmation.html.twig');
    }

    #[Route(path: '/login', name: 'admin_login')]
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

    #[Route(path: '/logout', name: 'admin_logout')]
    public function logoutAction(): void
    {
        // Left empty intentionally because this will be handled by Symfony.
    }

    #[Route(path: '/change-password', name: 'admin_change_password')]
    public function changePasswordAction(
        Request $request,
        UserFeedInterface $userFeed,
        ManagerRegistry $managerRegistry
    ): Response {
        $user = $this->getUser();

        if (!$this->needPasswordChange($request, $user)) {
            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }

        $form = $this->createForm(
            ChangePasswordForm::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManagerForClass($user::class)->flush();
            $userFeed->addToFeed($user, 'success', 'password_changed');

            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }

        return $this->render(
            '@DrawUser/security/reset.html.twig',
            ['form' => $form->createView()]
        );
    }

    private function needPasswordChange(Request $request, UserInterface $user): bool
    {
        if ($user instanceof PasswordChangeUserInterface && $user->getNeedChangePassword()) {
            return true;
        }

        if (!$request->query->has('t')) {
            return true;
        }

        if (!$user instanceof SecurityUserInterface) {
            return true;
        }

        if (null === $passwordUpdatedAt = $user->getPasswordUpdatedAt()) {
            return true;
        }

        return $passwordUpdatedAt->getTimestamp() < $request->query->getInt('t');
    }
}
