<?php

namespace Draw\Component\OpenApi\Doctrine;

use Doctrine\Common\Collections\ArrayCollection as BaseArrayCollection;

class ArrayCollectionMutationTracker extends BaseArrayCollection
{
    private $removedElements = [];

    public function getRemovedElements()
    {
        return $this->removedElements;
    }

    public function clearRemovedElements()
    {
        $this->removedElements = [];
    }

    public function remove($key)
    {
        $element = parent::remove($key);

        if (null !== $element) {
            $this->removedElements[] = $element;
        }

        return $element;
    }

    public function removeElement($element)
    {
        $result = parent::removeElement($element);
        if ($result) {
            $this->removedElements[] = $element;
        }

        return $result;
    }
}
