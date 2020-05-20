<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class Translatable
{
    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $domain;

    public function __construct($value = null)
    {
        if (!is_array($value)) {
            $value = ['token' => $value];
        }
        construct($this, $value);
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    public function __toString()
    {
        return $this->token;
    }

    public static function set($currentValue, $newValue)
    {
        if ($newValue instanceof Translatable) {
            return $newValue;
        }

        if (!$currentValue instanceof Translatable) {
            $currentValue = new Translatable();
        }

        $currentValue->setToken($newValue);

        return $currentValue;
    }
}