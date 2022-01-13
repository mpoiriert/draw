<?php

namespace Draw\Contracts\Application;

interface VersionVerificationInterface
{
    public function getRunningVersion(): ?string;

    public function getDeployedVersion(): ?string;

    public function isUpToDate(): bool;
}
