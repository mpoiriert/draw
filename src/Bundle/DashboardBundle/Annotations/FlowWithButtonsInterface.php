<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\Annotations\Button\Button;

interface FlowWithButtonsInterface
{
    /**
     * @return array|Button[]
     */
    public function getButtons(): array;
}