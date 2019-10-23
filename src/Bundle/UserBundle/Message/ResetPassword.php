<?php namespace Draw\Bundle\UserBundle\Message;

use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;

class ResetPassword implements ManuallyTriggeredInterface, AutoConnectInterface
{
    private $userId;

    private $urlToRedirectTo;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUrlToRedirectTo(): ?string
    {
        return $this->urlToRedirectTo;
    }

    public function setUrlToRedirectTo(string $url)
    {
        $this->urlToRedirectTo = $url;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function isSingleUseToken(): bool
    {
        return true;
    }
}