<?php namespace Draw\Bundle\UserBundle\Entity;

use DateTimeInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait SecurityUserTrait
{
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\Email()
     * @Assert\NotBlank()
     *
     * @Dashboard\Column(
     *     label="Email",
     *     visible=true,
     *     type="email",
     *     isActive=true,
     *     sortable=true
     * )
     */
    private $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string The plain password to update the password itself
     */
    private $plainPassword;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(name="last_password_updated_at", type="datetime_immutable", nullable=true)
     */
    private $passwordUpdatedAt;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = strtolower($email);
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        return ['ROLE_USER'];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(?string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
        if($this->plainPassword) {
            //This is needed to flag a property modified to trigger what's is needed for the flush
            $this->setPasswordUpdatedAt(new DateTimeImmutable());
        }
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getPasswordUpdatedAt(): ?DateTimeInterface
    {
        return $this->passwordUpdatedAt;
    }

    /**
     * @param \DateTimeImmutable $passwordUpdatedAt
     * @return SecurityUserTrait
     */
    public function setPasswordUpdatedAt(DateTimeInterface $passwordUpdatedAt)
    {
        $this->passwordUpdatedAt = \DateTimeImmutable::createFromFormat('U', $passwordUpdatedAt->getTimestamp());
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function __toString()
    {
        return $this->getUsername();
    }
}
