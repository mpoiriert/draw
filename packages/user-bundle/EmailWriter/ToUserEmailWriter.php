<?php

namespace Draw\Bundle\UserBundle\EmailWriter;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Email\ToUserEmailInterface;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Symfony\Component\Mime\Email;

class ToUserEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return ['compose' => -255];
    }

    public function __construct(private EntityRepository $drawUserEntityRepository)
    {
    }

    public function compose(ToUserEmailInterface $email): void
    {
        if (!$email instanceof Email || $email->getTo()) {
            return;
        }

        $user = $this->drawUserEntityRepository->find($email->getUserId());

        if (!$user) {
            return;
        }

        $email->to($user->getEmail());
    }
}
