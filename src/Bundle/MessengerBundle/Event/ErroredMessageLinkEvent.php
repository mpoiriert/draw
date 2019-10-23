<?php namespace Draw\Bundle\MessengerBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ErroredMessageLinkEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var \Throwable
     */
    private $error;

    /**
     * @var Response
     */
    private $response;

    public function __construct(Request $request, $messageId, \Throwable $exception)
    {
        $this->request = $request;
        $this->messageId = $messageId;
        $this->error = $exception;
    }

    /**
     * @return Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}