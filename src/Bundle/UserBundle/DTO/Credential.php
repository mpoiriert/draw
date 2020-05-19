<?php namespace Draw\Bundle\UserBundle\DTO;

use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Credential
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Dashboard\FormInput(
     *     type="string",
     *     label="_drawUserBundle.credential.username"
     * )
     *
     * @Serializer\Type("string")
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Dashboard\FormInput(
     *     type="password",
     *     label="_drawUserBundle.credential.password"
     * )
     *
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
}