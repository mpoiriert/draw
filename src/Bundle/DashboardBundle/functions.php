<?php namespace Draw\Bundle\DashboardBundle;

use JMS\Serializer\SerializationContext;

if(!function_exists('Draw\Bundle\DashboardBundle\construct')) {
    function construct($object, array $values)
    {
        foreach ($values as $k => $v) {
            if (!method_exists($object, $name = 'set' . $k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for object "@%s".', $k, get_class($object)));
            }

            $object->$name($v);
        }
    }

    function first_parent(SerializationContext $context, $class)
    {
        foreach($context->getVisitingStack() as $object) {
            if($object instanceof $class) {
                return $object;
            }
        }
        return null;
    }
}

