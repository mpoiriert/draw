<?php

namespace Draw\Bundle\SonataExtraBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Timezone as TimezoneConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TimeZoneSubscriber implements EventSubscriberInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [
                ['setTimeZone', -255],
            ],
        ];
    }

    public function setTimeZone(RequestEvent $event): void
    {
        $timeZone = $event->getRequest()->cookies->get('adminUserTimezone');
        $violations = $this->validator->validate($timeZone, [new NotBlank(), new TimezoneConstraint()]);
        if (0 === $violations->count()) {
            date_default_timezone_set($timeZone);
        }
    }
}
