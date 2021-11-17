<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

interface CanBeExcludeInterface
{
    public function getExcludeIf(): ?string;
}
