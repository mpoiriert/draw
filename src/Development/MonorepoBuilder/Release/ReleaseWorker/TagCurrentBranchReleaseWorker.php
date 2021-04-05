<?php

namespace Draw\Development\MonorepoBuilder\Release\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Throwable;

class TagCurrentBranchReleaseWorker implements ReleaseWorkerInterface
{
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function work(Version $version): void
    {
        try {
            $this->processRunner->run(sprintf(
                'git add . && git commit -m "prepare release %s" && git push origin master',
                $version->getVersionString()
            ));
        } catch (Throwable $throwable) {
            // nothing to commit
        }

        $this->processRunner->run('git tag ' . $version->getVersionString());
    }

    public function getDescription(Version $version): string
    {
        return sprintf('Add local tag to the current branch "%s"', $version->getVersionString());
    }
}
