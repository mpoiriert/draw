<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

interface BatchActionInterface
{
    /**
     * Return a callable that will be called for each object in the batch.
     *
     * First argument will be the admin object, second argument will be the object.
     *
     * @return callable{ActionableInterface, object}: void
     */
    public function getBatchCallable(): callable;
}
