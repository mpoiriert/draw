<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ChildObject2 extends BaseObject
{
    /**
     * @ORM\Column(name="attribute_2", type="string")
     */
    private $attribute2;

    public function getAttribute2()
    {
        return $this->attribute2;
    }

    public function setAttribute2($attribute2): void
    {
        $this->attribute2 = $attribute2;
    }
}
