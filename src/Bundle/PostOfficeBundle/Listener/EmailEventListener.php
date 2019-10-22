<?php namespace Draw\Bundle\PostOfficeBundle\Listener;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\RawMessage;

class EmailEventListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $serviceLocator;

    private $writers = [];

    private $sortedWriters = [];

    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class =>
                ['composeMessage', 200]
        ];
    }

    public function __construct(ContainerInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function addWriter(string $email, $writerName, $writerMethod, $priority = 0)
    {
        $this->writers[$email][$priority][] = [$writerName, $writerMethod];
        unset($this->sortedWriters[$email]);
    }

    public function getWriters(string $email = null)
    {
        if(!is_null($email)) {
            if (empty($this->writers[$email])) {
                return [];
            }

            if(!isset($this->sortedWriters[$email])) {
                $this->sortWriters($email);
            }

            return $this->sortedWriters[$email];
        }
    }

    private function sortWriters(string $email)
    {
        krsort($this->writers[$email]);
        $this->sortedWriters[$email] = array_merge(...$this->writers[$email]);
    }

    public function composeMessage(MessageEvent $messageEvent)
    {
        $message = $messageEvent->getMessage();
        $envelope = $messageEvent->getEnvelope();
        foreach($this->getTypes($message) as $type) {
            foreach($this->getWriters($type) as $writer) {
                list($writerName, $writerMethod) = $writer;
                $service = $this->serviceLocator->get($writerName);
                call_user_func([$service, $writerMethod], $message, $envelope);
            }
        }
    }

    private function getTypes(RawMessage $message)
    {
        return [get_class($message)]
            + class_parents($message)
            + class_implements($message);
    }
}