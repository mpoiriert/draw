<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_acme__tag")
 *
 * @UniqueEntity(fields={"label"})
 */
class Tag
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     *
     * @Assert\NotNull()
     * @Assert\Length(min=3, max=255, allowEmptyString=false)
     *
     * @Dashboard\Column(label="Label")
     *
     * @Dashboard\FormInput(
     *     type="string",
     *     label="Label"
     * )
     */
    private $label;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function __toString()
    {
        return (string)$this->getLabel();
    }
}