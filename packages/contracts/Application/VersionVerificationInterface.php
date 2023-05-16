<?php

namespace Draw\Contracts\Application;

use Draw\Contracts\Application\Exception\VersionInformationIsNotAccessibleException;

interface VersionVerificationInterface
{
    /**
     * @throws VersionInformationIsNotAccessibleException
     */
    public function getRunningVersion(): ?string;

    /**
     * @throws VersionInformationIsNotAccessibleException
     */
    public function getDeployedVersion(): ?string;

    /**
     * @throws VersionInformationIsNotAccessibleException
     */
    public function isUpToDate(): bool;
}
