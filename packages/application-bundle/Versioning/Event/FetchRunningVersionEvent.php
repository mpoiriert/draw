<?php

namespace Draw\Bundle\ApplicationBundle\Versioning\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FetchRunningVersionEvent extends Event
{
    private $runningVersion = null;

    public function getRunningVersion(): ?string
    {
        return $this->runningVersion;
    }

    public function setRunningVersion(string $value): void
    {
        $this->runningVersion = $value;
        $this->stopPropagation();
    }
}
