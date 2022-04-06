<?php

namespace Draw\Bundle\UserBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\AccountLocker\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait SecurityUserTrait
{
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    private ?string $email = null;

    /**
     * The hashed password.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $password = null;

    /**
     * The plain password to update the password itself.
     */
    private ?string $plainPassword = null;

    /**
     * @ORM\Column(name="last_password_updated_at", type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $passwordUpdatedAt = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->getUsername();
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        return ['ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        if ($this->password === $password) {
            return $this;
        }

        $this->password = $password;

        if (!$this->password) {
            return $this;
        }

        if ($this instanceof PasswordChangeUserInterface) {
            $this->setNeedChangePassword(false);
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        if ($this->plainPassword) {
            // This is needed to flag a property modified to trigger what's is needed for the flush
            // We want to make sure the date change in case the previous value is on the same second
            $this->passwordUpdatedAt = null;
            $this->setPasswordUpdatedAt(new DateTimeImmutable());
        }

        return $this;
    }

    public function getPasswordUpdatedAt(): ?DateTimeInterface
    {
        return $this->passwordUpdatedAt;
    }

    public function setPasswordUpdatedAt(DateTimeInterface $passwordUpdatedAt): self
    {
        switch (true) {
            case null === $this->passwordUpdatedAt:
            case $this->passwordUpdatedAt->getTimestamp() !== $passwordUpdatedAt->getTimestamp():
                $this->passwordUpdatedAt = DateTimeImmutable::createFromFormat('U', $passwordUpdatedAt->getTimestamp());
                if ($this instanceof LockableUserInterface) {
                    $this->unlock(UserLock::REASON_PASSWORD_EXPIRED);
                }
                break;
        }

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function __toString()
    {
        return $this->getUserIdentifier();
    }
}
