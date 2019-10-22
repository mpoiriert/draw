<?php namespace Draw\Bundle\PostOfficeBundle\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunicationLocaleWriter implements EmailWriterInterface
{
    private $defaultCommunicationLocale;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public static function getForEmails(): array
    {
        return [
            'assignCommunicationLocaleToContext' => -255
        ];
    }

    public function __construct(
        ?RequestStack $requestStack,
        string $defaultCommunicationLocale
    ) {
        $this->requestStack = $requestStack;
        $this->defaultCommunicationLocale = $defaultCommunicationLocale;
    }

    private function getCommunicationLocale()
    {
        if($this->requestStack && $request = $this->requestStack->getCurrentRequest()) {
            return $request->getLocale();
        }

        return $this->defaultCommunicationLocale;
    }

    public function assignCommunicationLocaleToContext(TemplatedEmail $email)
    {
        $email->context(
            $email->getContext() + [
                'default_communication_locale' => $this->defaultCommunicationLocale,
                'communication_locale' => $this->getCommunicationLocale()
            ]
        );
    }
}