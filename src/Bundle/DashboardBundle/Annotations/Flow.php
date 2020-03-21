<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JsonSerializable;

/**
 * @Annotation
 */
abstract class Flow implements JsonSerializable
{
    public $type;

    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }
}