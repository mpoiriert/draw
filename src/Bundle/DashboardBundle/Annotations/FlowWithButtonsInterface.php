<?php namespace Draw\Bundle\DashboardBundle\Annotations;

interface FlowWithButtonsInterface
{
    /**
     * @return array|Button[]
     */
    public function getButtons(): array;
}