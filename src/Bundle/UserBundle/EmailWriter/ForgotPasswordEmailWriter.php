<?php namespace Draw\Bundle\UserBundle\EmailWriter;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\PostOfficeBundle\Email\EmailWriterInterface;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordEmailWriter implements EmailWriterInterface
{
    /**
     * @var EntityRepository
     */
    private $userEntityRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public static function getForEmails(): array
    {
        return ['compose' => 255];
    }

    public function __construct(
        EntityRepository $userEntityRepository,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->userEntityRepository = $userEntityRepository;
    }

    public function compose(ForgotPasswordEmail $forgotPasswordEmail)
    {
        $forgotPasswordEmail
            ->to($email = $forgotPasswordEmail->getEmailAddress());

        /** @var SecurityUserInterface $user */
        $user = $this->userEntityRepository
            ->findOneBy(['email' => $email]);

        if($user) {
            $forgotPasswordEmail->user($user);
        } else {
            $forgotPasswordEmail
                ->htmlTemplate('@DrawUser/Email/reset_password_email_user_not_found.html.twig')
                ->callToActionLink(
                    $this->urlGenerator->generate('invite_create_account', [], UrlGeneratorInterface::ABSOLUTE_URL)
                );
        }
    }
}