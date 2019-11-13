<?php namespace Draw\Bundle\UserBundle\Sonata\Controller;

use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Draw\Bundle\UserBundle\Sonata\Form\AdminLoginForm;
use Draw\Bundle\UserBundle\Sonata\Form\ChangePasswordForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends AbstractController
{
    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @Route("/resetting/forgot-password", name="admin_forgot_password", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param MailerInterface $mailer
     *
     * @return Response
     */
    public function forgotPasswordAction(
        Request $request,
        MailerInterface $mailer
    ): Response {
        if ($request->getMethod() == Request::METHOD_GET) {
            return $this->render('@DrawUser/security/forgot_password.html.twig');
        }

        $mailer->send(new ForgotPasswordEmail($request->request->get('username')));

        return new RedirectResponse($this->generateUrl('admin_check_email'));
    }

    /**
     * @Route("/resetting/check-email", name="admin_check_email")
     *
     * @return Response
     */
    public function checkEmailAction(): Response
    {
        return $this->render('@DrawUser/security/check_email.html.twig');
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
    public function changePasswordAction(Request $request, UserFeedInterface $userFeed): Response
    {
        /** @var UserInterface $user */
        $user = $this->getUser();
        $form = $this->createForm(
            ChangePasswordForm::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManagerForClass(get_class($user))->flush();
            $userFeed->addToFeed($user, 'success', 'Password change');
            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }

        return $this->render(
            '@DrawUser/security/reset.html.twig',
            ['form' => $form->createView()]
        );
    }
}