<?php

namespace Draw\Bundle\ApplicationBundle\Versioning\Listener;

use Draw\Bundle\ApplicationBundle\Versioning\Event\FetchRunningVersionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FetchRunningVersionListener implements EventSubscriberInterface
{
    private $projectDirectory;

    public static function getSubscribedEvents()
    {
        return [
            FetchRunningVersionEvent::class => [
                ['fetchFromFilesystemPublicVersion', 255],
                ['fetchFromGit', -10],
            ],
        ];
    }

    public function __construct(?string $projectDirectory)
    {
        $this->projectDirectory = $projectDirectory;
    }

    public function fetchFromFilesystemPublicVersion(FetchRunningVersionEvent $event): void
    {
        switch (true) {
            case null === $this->projectDirectory:
            case !file_exists($versionFilename = $this->projectDirectory.'/public/version.txt'):
                return;
        }

        $event->setRunningVersion(trim(file_get_contents($versionFilename)));
    }

    public function fetchFromGit(FetchRunningVersionEvent $event): void
    {
        switch (true) {
            case null === $this->projectDirectory:
            case !file_exists($this->projectDirectory.'/.git'):
                return;
        }

        $version = exec(
            sprintf(
                '(cd %s && git describe --tags --always --dirty) 2>&1',
                $this->projectDirectory
            ),
            $output,
            $code
        );

        switch (true) {
            case 0 !== $code:
            case 2 >= mb_strlen($version):
                return;
        }

        $event->setRunningVersion(preg_replace('/[[:^print:]]/', '', $version));
    }
}
