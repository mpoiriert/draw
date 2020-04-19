<?php namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

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

    const LEVEL_USER = 'user';

    const LEVEL_ADMIN = 'admin';

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     *
     * @Dashboard\Column(
     *      sortable=true,
     *      label="#"
     * )
     *
     * @Serializer\ReadOnly()
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var Tag[]|Collection
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\Tag"
     * )
     *
     * @Dashboard\Column(
     *     type="list",
     *     label="Tags",
     *     sortable=false,
     *     options={"list": {"attribute":"label"}}
     * )
     *
     * @Dashboard\FormInputChoices(
     *     label="Tags",
     *     multiple=true,
     *     repositoryMethod="findActive"
     * )
     */
    private $tags;

    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", nullable=false, options={"default":"user"})
     *
     * @Dashboard\Column(
     *     type="choices",
     *     label="Level",
     *     options={"choices"={"user":"User", "admin":"Admin"}}
     * )
     *
     * @Dashboard\FormInputChoices(
     *     label="Level",
     *     choices={"User":"user", "Admin":"admin"}
     * )
     */
    private $level = 'user';

    /**
     * @var Address
     *
     * @ORM\Embedded(class="App\Entity\Address", columnPrefix="address_")
     *
     * @Assert\Valid()
     *
     * @Dashboard\FormInput(
     *     type="composite",
     *     label="Address"
     * )
     */
    private $address;

    public function __construct()
    {
        $this->address = new Address();
        $this->tags = new ArrayCollection();
    }

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

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // guarantee every user at least has ROLE_USER
        return $roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return Tag[]|Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[]|Collection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }
}