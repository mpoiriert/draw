<?php

namespace Draw\Bundle\SonataExtraBundle\Security\Voter;

class Relation
{
    public function __construct(private $class, private $relatedClass, private $path)
    {
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getRelatedClass()
    {
        return $this->relatedClass;
    }

    public function getPath()
    {
        return $this->path;
    }
}
