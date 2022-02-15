<?php

namespace Draw\Bundle\UserBundle\EmailWriter;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Bundle\PostOfficeBundle\Email\EmailWriterInterface;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Message\ResetPassword;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordEmailWriter implements EmailWriterInterface
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var EntityRepository
     */
    private $userEntityRepository;

    private $resetPasswordRoute;

    private $inviteCreateAccountRoute;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public static function getForEmails(): array
    {
        return ['compose' => 255];
    }

    public function __construct(
        EntityRepository $drawUserEntityRepository,
        ?MessageBusInterface $messageBus,
        UrlGeneratorInterface $urlGenerator,
        string $resetPasswordRoute,
        string $inviteCreateAccountRoute
    ) {
        $this->messageBus = $messageBus;
        $this->urlGenerator = $urlGenerator;
        $this->inviteCreateAccountRoute = $inviteCreateAccountRoute;
        $this->resetPasswordRoute = $resetPasswordRoute;
        $this->userEntityRepository = $drawUserEntityRepository;
    }

    public function compose(ForgotPasswordEmail $forgotPasswordEmail)
    {
        $forgotPasswordEmail
            ->to($email = $forgotPasswordEmail->getEmailAddress());

        /** @var SecurityUserInterface $user */
        $user = $this->userEntityRepository
            ->findOneBy(['email' => $email]);

        if ($user) {
            $forgotPasswordEmail
                ->user($user);

            if ($this->messageBus) {
                $messageId = $this->messageBus
                    ->dispatch(
                        new ResetPassword($user->getId(), $this->resetPasswordRoute),
                        [new ExpirationStamp(new \DateTime('+ 1 days'))]
                    )
                    ->last(TransportMessageIdStamp::class)
                    ->getId();

                $url = $this->urlGenerator
                    ->generate(
                        'message_click',
                        [
                            MessageController::MESSAGE_ID_PARAMETER_NAME => $messageId,
                            'type' => 'reset_password',
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                $forgotPasswordEmail->callToActionLink($url);
            }
        } else {
            $forgotPasswordEmail
                ->htmlTemplate('@DrawUser/Email/reset_password_email_user_not_found.html.twig')
                ->callToActionLink(
                    $this->urlGenerator->generate($this->inviteCreateAccountRoute, [], UrlGeneratorInterface::ABSOLUTE_URL)
                );
        }
    }
}
