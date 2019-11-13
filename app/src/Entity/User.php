<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_acme__user")
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity(fields={"email"})
 */
class User implements SecurityUserInterface
{
    use SecurityUserTrait;

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @return string
     *
     * @ORM\PrePersist()
     */
    public function getId()
    {
        if(is_null($this->id)) {
            $this->id = Uuid::uuid4()->toString();
        }

        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}