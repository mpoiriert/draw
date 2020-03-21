<?php namespace App\DTO;

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
     *     type="email",
     *     label="Email"
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
     *     label="Password"
     * )
     *
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}