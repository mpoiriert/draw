<?php namespace Draw\Bundle\PostOfficeBundle\Listener;

use Psr\Container\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;
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
            MessageEvent::class => [
                ['composeMessage', 200],
                ['assignSubjectFromHtmlTitle', -2]
            ]
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
        if(!$message instanceof Message) {
            return;
        }

        if($message->getHeaders()->has('X-DrawPostOffice')) {
            return;
        }

        $message->getHeaders()->add(new UnstructuredHeader('X-DrawPostOffice', 1));

        $envelope = $messageEvent->getEnvelope();

        foreach($this->getTypes($message) as $type) {
            foreach($this->getWriters($type) as $writer) {
                list($writerName, $writerMethod) = $writer;
                $service = $this->serviceLocator->get($writerName);
                call_user_func([$service, $writerMethod], $message, $envelope);
            }
        }
    }

    public function assignSubjectFromHtmlTitle(MessageEvent $messageEvent)
    {
        $message = $messageEvent->getMessage();
        if(!$message instanceof Email) {
            return;
        }

        switch(true) {
            case !($body = $message->getHtmlBody()):
            case !count($crawler = (new Crawler($body))->filter('html > head > title')->first()):
            case !($subject = $crawler->text()):
                return;
        }

        $message->subject($subject);
    }

    private function getTypes(RawMessage $message)
    {
        return [get_class($message)]
            + class_parents($message)
            + class_implements($message);
    }
}