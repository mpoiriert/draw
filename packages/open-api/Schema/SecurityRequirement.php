<?php

namespace Draw\Component\OpenApi\Schema;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class SecurityRequirement
{
    /**
     * @var mixed
     */
    private $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Each name must correspond to a security scheme which is declared in the Security Definitions.
     * If the security scheme is of type "oauth2", then the value is a list of scope names required for the execution.
     * For other security scheme types, the array MUST be empty.
     *
     * @param string[] $value
     */
    public function __set(string $name, array $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get($name): ?array
    {
        return $this->data[$name];
    }
}
