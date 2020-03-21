<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionList extends Action
{
    public $paginated = true;

    public function getType()
    {
        return 'list';
    }
}