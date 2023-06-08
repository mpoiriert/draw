<?php

namespace Draw\Bundle\SonataExtraBundle;

interface ListPriorityAwareAdminInterface
{
    /**
     * @return array{defaultMaxField: int|null, defaultFieldPriorities: array<string, int>}
     */
    public function getListFieldPriorityOptions(): array;
}
