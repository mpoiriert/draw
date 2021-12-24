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
     * @var string
     */
    private $timezone;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(string $timezone, ValidatorInterface $validator)
    {
        $this->timezone = $timezone;
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
        $timeZone = $event->getRequest()->cookies->get('timezone');
        $violations = $this->validator->validate($timeZone, [new NotBlank(), new TimezoneConstraint()]);
        if ($violations->count() > 0) {
            $timeZone = $this->timezone;
        }

        date_default_timezone_set($timeZone);
    }
}
