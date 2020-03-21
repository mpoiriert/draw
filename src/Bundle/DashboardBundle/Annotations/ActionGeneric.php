<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionGeneric extends Action
{
    public function getType()
    {
        return 'generic';
    }
}