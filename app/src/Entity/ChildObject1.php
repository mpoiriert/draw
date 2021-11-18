<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ChildObject1 extends BaseObject
{
    /**
     * @ORM\Column(name="attribute_1", type="string")
     */
    private $attribute1;

    public function getAttribute1()
    {
        return $this->attribute1;
    }

    public function setAttribute1($attribute1): void
    {
        $this->attribute1 = $attribute1;
    }
}
